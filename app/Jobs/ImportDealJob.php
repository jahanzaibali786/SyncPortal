<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadPipeline;
use App\Traits\ExcelImportable;
use App\Traits\UniversalSearchTrait;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ImportDealJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait, ExcelImportable;

    private $row;
    private $columns;
    private $company;

    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    public function handle()
    {
        $leadCount = Session::get('total_leads', 1);
        Session::put('total_leads', $leadCount + 1);

        // ✅ Required columns based on new DealImport
        if (
            $this->isColumnExists('lead_contact_email') ||
            $this->isColumnExists('phone_number')
        ) {
            // Get pipeline info
            if (
                !$this->isColumnExists('deal_name') ||
                !$this->isColumnExists('pipeline') ||
                !$this->isColumnExists('deal_stage') ||
                !$this->isColumnExists('value') ||
                !$this->isColumnExists('close_date')
            ) {
                $this->failJob(__('messages.invalidData'));
                return;
            }

            // ✅ Fetch or create lead (by email or phone)
            $leadQuery = Lead::withoutGlobalScopes()->where('company_id', $this->company?->id);

            if ($this->getColumnValue('lead_contact_email')) {
                $leadQuery->where('client_email', $this->getColumnValue('lead_contact_email'));
            } elseif ($this->getColumnValue('phone_number')) {
                $leadQuery->where('mobile', $this->getColumnValue('phone_number'));
            }

            $lead = $leadQuery->first();

            if (!$lead) {
                // Create a new lead automatically
                $lead = new Lead();
                $lead->company_name = $this->getColumnValue('client_name') ?? 'Unknown';
                $lead->client_email = $this->getColumnValue('lead_contact_email');
                $lead->mobile = $this->getColumnValue('phone_number');
                $lead->client_name = $this->getColumnValue('client_name') ?? 'Unknown';
                $lead->company_id = $this->company?->id;
                $lead->save();
            }

            // ✅ Find or fallback pipeline
            $pipeline = LeadPipeline::withoutGlobalScopes()
                ->where('name', $this->getColumnValue('pipeline'))
                ->where('company_id', $this->company?->id)
                ->first();

            if (!$pipeline) {
                $pipeline = LeadPipeline::withoutGlobalScopes()
                    ->where('company_id', $this->company?->id)
                    ->first();
            }

            if (!$pipeline) {
                $this->failJob(__('messages.invalidData'));
                return;
            }

            // ✅ Find or fallback stage
            $stage = $pipeline->stages->where('name', $this->getColumnValue('deal_stage'))->first();
            if (!$stage) {
                $stage = $pipeline->stages->where('default', 1)->first();
            }

            if (!$stage) {
                $this->failJob(__('messages.invalidData'));
                return;
            }

            DB::beginTransaction();
            Session::put('is_imported', true);

            try {
                // ✅ Create Deal
                $deal = new Deal();
                $deal->name = $this->getColumnValue('deal_name');
                $deal->lead_id = $lead->id;
                $deal->next_follow_up = 'yes';
                $deal->lead_pipeline_id = $pipeline->id;
                $deal->pipeline_stage_id = $stage->id;
                $deal->close_date = Carbon::parse($this->getColumnValue('close_date') ?: Carbon::now())->format('Y-m-d');
                $deal->value = $this->getColumnValue('value') ?: 0;
                $deal->currency_id = $this->company->currency_id;
                $deal->save();

                // ✅ Update session leads list
                $leads = Session::get('leads', []);
                $leads[] = [
                    'deal_name' => $deal->name,
                    'email' => $lead->client_email,
                    'phone' => $lead->mobile,
                ];
                Session::put('leads', $leads);

                // ✅ Log search
                $this->logSearchEntry($deal->id, $deal->name, 'deals.show', 'deal');

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }
        } else {
            $this->failJob(__('messages.invalidData'));
        }
    }
}

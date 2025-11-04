<?php

namespace App\DataTables;

use App\Models\LeadAgent;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;

class LeadSubAgentReportDatatable extends BaseDataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('employee_name', fn($row) => $row->agent_name)
            ->addColumn('total_deals', fn($row) => $row->count_total_leads)
            ->addColumn('won_deals', fn($row) => $row->count_won_leads)
            ->addColumn('lost_deals', fn($row) => $row->total_lost_deals)
            ->addColumn('total_amount', fn($row) => currency_format($row->total_value, company()->currency_id))
            ->addColumn('converted_amount', fn($row) => $row->total_converted_value ? currency_format($row->total_converted_value, company()->currency_id) : 0)
            ->addColumn('total_follow_up', fn($row) => $row->count_total_follow_up)
            ->addColumn('total_pending_follow_up', fn($row) => $row->count_total_pending_follow_up)
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['total_leads', 'action', 'converted_lead', 'total_amount', 'converted_amount']);
    }

    /**
     * Build query for Sub Agent Report.
     *
     * @param LeadAgent $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(LeadAgent $model)
    {
        $request = $this->request();

        $startDate = $request->startDate ? companyToDateString($request->startDate) : null;
        $endDate = $request->endDate ? companyToDateString($request->endDate) : null;

        $query = DB::table('users')
            ->select(
                'users.id',
                'users.name as agent_name',
                DB::raw('COUNT(DISTINCT deals.id) as count_total_leads'),
                DB::raw("COUNT(DISTINCT CASE WHEN pipeline_stages.slug = 'win' THEN deals.id END) as count_won_leads"),
                DB::raw("COUNT(DISTINCT CASE WHEN pipeline_stages.slug = 'lost' THEN deals.id END) as total_lost_deals"),
                DB::raw("SUM(deals.value) as total_value"),
                DB::raw("SUM(CASE WHEN pipeline_stages.slug = 'win' THEN deals.value ELSE 0 END) as total_converted_value"),
                DB::raw("COUNT(DISTINCT lead_follow_up.id) as count_total_follow_up"),
                DB::raw("COUNT(DISTINCT CASE WHEN lead_follow_up.status = 'pending' THEN lead_follow_up.id END) as count_total_pending_follow_up")
            )
            ->leftJoin('deals', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(users.id, deals.sub_agents)'), '>', DB::raw('0'));
            })
            ->leftJoin('pipeline_stages', 'deals.pipeline_stage_id', '=', 'pipeline_stages.id')
            ->leftJoin('lead_follow_up', 'lead_follow_up.deal_id', '=', 'deals.id')
            ->join('lead_agents', 'lead_agents.user_id', '=', 'users.id')
            ->whereNotNull('deals.id');

        if ($startDate) {
            $query->where(DB::raw('DATE(deals.close_date)'), '>=', $startDate);
        }

        if ($endDate) {
            $query->where(DB::raw('DATE(deals.close_date)'), '<=', $endDate);
        }

        // Optional: if you want to filter by a specific subagent
        if (!empty($request->agent) && $request->agent !== 'all') {
            $query->where('users.id', $request->agent);
        }

        $query->groupBy('users.id');

        return $model->setQuery($query);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        $dataTable = $this->setBuilder('lead-subagent-report-table', 5)
            ->parameters([
                'scrollX' => true,
                'autoWidth' => false,
                'responsive' => false,
                'initComplete' => 'function () {
                window.LaravelDataTables["lead-subagent-report-table"].buttons().container()
                .appendTo("#table-actions")
            }',
                'fnDrawCallback' => 'function() {
                $(".select-picker").selectpicker();
            }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make([
                'extend' => 'excel',
                'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel'),
            ]));
        }

        return $dataTable;
    }


    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'title' => __('app.id')],
            __('modules.deal.dealAgent') => ['data' => 'employee_name', 'name' => 'users.name', 'title' => __('modules.deal.dealAgent')],
            __('modules.deal.totalDeals') => ['data' => 'total_deals', 'name' => 'count_total_leads', 'title' => __('modules.deal.totalDeals')],
            __('modules.deal.wonDeals') => ['data' => 'won_deals', 'name' => 'count_won_leads', 'title' => __('modules.deal.wonDeals')],
            __('modules.deal.lostDeals') => ['data' => 'lost_deals', 'name' => 'total_lost_deals', 'title' => __('modules.deal.lostDeals')],
            __('app.totalAmount') => ['data' => 'total_amount', 'name' => 'total_value', 'title' => __('app.totalAmount')],
            __('modules.lead.convertedAmount') => ['data' => 'converted_amount', 'name' => 'total_converted_value', 'title' => __('modules.lead.convertedAmount')],
            __('app.total') . ' ' . __('app.followUp') => ['data' => 'total_follow_up', 'name' => 'count_total_follow_up', 'title' => __('app.total') . ' ' . __('app.followUp')],
            __('app.total') . ' ' . __('app.pending') . ' ' . __('app.followUp') => ['data' => 'total_pending_follow_up', 'name' => 'count_total_pending_follow_up', 'title' => __('app.total') . ' ' . __('app.pending') . ' ' . __('app.followUp')],
        ];
    }

    public function pdf()
    {
        set_time_limit(0);

        if (config('datatables-buttons.pdf_generator', 'snappy') === 'snappy') {
            return $this->snappyPdf();
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

        return $pdf->download($this->getFilename() . '.pdf');
    }
}

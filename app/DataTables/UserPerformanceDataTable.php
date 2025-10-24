<?php

namespace App\DataTables;

use App\Models\Deal;
use App\Models\GoogleMeetings;
use App\Models\LeadCall;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use Illuminate\Http\JsonResponse; // make sure this import is at the top

class UserPerformanceDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->collection($query)
            ->addColumn('answered_calls', fn($row) => $row['answered'] ?? 0)
            ->editColumn('avg_duration', function ($row) {
                return ($row['avg_duration'] > 0)
                    ? gmdate("i:s", $row['avg_duration']) . ' min'
                    : '0 sec';
            })
            ->editColumn('conversion_rate', function ($row) {
                $rate = $row['conversion_rate'] ?? 0;
                $color = $rate >= 50 ? 'success' : ($rate >= 20 ? 'warning' : 'danger');
                return '<span class="badge bg-' . $color . '">' . number_format($rate, 1) . '%</span>';
            })
            ->rawColumns(['conversion_rate']); // ✅ Removed ->make(true)
    }

    public function query(Request $request)
    {
        $user = Auth::user();
        $start = $request->get('start_date') ?? now()->startOfMonth()->format('Y-m-d');
        $end = $request->get('end_date') ?? now()->endOfDay()->format('Y-m-d');

        $leads = Deal::pluck('id');
        $leadSourcesMap = LeadSource::pluck('id')->toArray();

        // ✅ Get all BDO users
        $query = User::join('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'BD')
            ;

        $startOfMonth = date('Y-m-d', strtotime($start));
        $endOfMonth = date('Y-m-d', strtotime($end));

        $query->where(function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereNull('employee_details.termination_date')
                ->orWhereBetween('employee_details.termination_date', [$startOfMonth, $endOfMonth]);
        });

        $agents = $query->select('users.*')->get();
        // dd($agents);
        $performance = [];

        foreach ($agents as $agent) {
            $callsQuery = LeadCall::where('user_id', $agent->id)
                ->whereIn('lead_id', $leads);

            if (!empty($start)) {
                $callsQuery->where('created_at', '>=', $start . ' 00:00:00');
            }
            if (!empty($end)) {
                $callsQuery->where('created_at', '<=', $end . ' 23:59:59');
            }

            if ($request->filled('status')) {
                $callsQuery->where('status', $request->status);
            }

            $calls = $callsQuery->get();

            $totalCalls = $calls->count();
            $avgDuration = $totalCalls > 0 ? $calls->avg('duration') : 0;
            $calledLeadIds = $calls->pluck('lead_id')->unique()->toArray();
            $totalLeads = count($calledLeadIds);
            $answeredCalls = $calls->where('status', '200')->count();

            // --- Conversion logic ---
            $convertedLeads = collect();
            $callsByDate = $calls->groupBy(fn($call) => date('Y-m-d', strtotime($call->created_at)));
            $processedLeads = [];
            // dd($callsByDate);
            foreach ($callsByDate as $callDate => $dailyCalls) {
                foreach ($dailyCalls as $call) {
                    $leadId = $call->lead_id;
                    if (in_array($leadId, $processedLeads)) continue;

                    $meetingOnCallDate = GoogleMeetings::whereRaw("FIND_IN_SET(?, assigned_to)", [$agent->id])
                        ->where('lead_id', $leadId)
                        ->whereDate('start', $callDate)
                        ->first();

                    if ($meetingOnCallDate) {
                        $previousMeetings = GoogleMeetings::whereRaw("FIND_IN_SET(?, assigned_to)", [$agent->id])
                            ->where('lead_id', $leadId)
                            ->whereDate('start', '<', $callDate)
                            ->exists();

                        if (!$previousMeetings) {
                            $convertedLeads->push(['lead_id' => $leadId]);
                            $processedLeads[] = $leadId;
                        }
                    } else {
                        $futureMeeting = GoogleMeetings::whereRaw("FIND_IN_SET(?, assigned_to)", [$agent->id])
                            ->where('lead_id', $leadId)
                            ->whereDate('start', '>', $callDate)
                            ->orderBy('start')
                            ->first();

                        if ($futureMeeting) {
                            $previousMeetings = GoogleMeetings::whereRaw("FIND_IN_SET(?, assigned_to)", [$agent->id])
                                ->where('lead_id', $leadId)
                                ->whereDate('start', '<', $callDate)
                                ->exists();

                            if (!$previousMeetings) {
                                $convertedLeads->push(['lead_id' => $leadId]);
                                $processedLeads[] = $leadId;
                            }
                        }
                    }
                }
            }
            // dd($convertedLeads);
            // if($agent->name == 'Shaheer Hassan'){
            //     dd($convertedLeads,$totalCalls,$totalConvertedLeads,$answeredCalls,$totalLeads);
            // }   
            $totalConvertedLeads = $convertedLeads->count();
            $conversionRate = $totalLeads > 0 ? ($totalConvertedLeads / $totalLeads) * 100 : 0;

            $performance[] = [
                'agent_name' => $agent->name,
                'total_calls' => $totalCalls,
                'avg_duration' => round($avgDuration, 2),
                'converted_leads' => $totalConvertedLeads,
                'total_leads' => $totalLeads,
                'answered' => $answeredCalls,
                'conversion_rate' => round($conversionRate, 2),
            ];
        }

        // ✅ Return as a collection
        return collect($performance);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('lead-calls-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'responsive' => true,
                'processing' => true,
                'serverSide' => true,
                'autoWidth' => false,
                'pageLength' => 25,
                'order' => [[0, 'asc']],
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('agent_name')->title('Agent Name'),
            Column::make('total_calls')->title('Total Calls Made'),
            Column::make('answered_calls')->title('Answered Calls'),
            Column::make('total_leads')->title('Total Leads'),
            Column::make('avg_duration')->title('Average Duration'),
            Column::make('converted_leads')->title('Converted Leads'),
            Column::make('conversion_rate')->title('Conversion Rate'),
        ];
    }
    public function ajax(): JsonResponse
    {
        return $this->dataTable($this->query(request()))->toJson();
    }
}

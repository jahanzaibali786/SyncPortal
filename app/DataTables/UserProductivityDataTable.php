<?php

namespace App\DataTables;

use App\Models\LeadCall;
use App\Models\User;
use Yajra\DataTables\Services\DataTable;

class UserProductivityDataTable extends DataTable
{
    public function dataTable($query)
    {
        $unknownCounter = 1;
        $unknownMap = [];

        return datatables()
            ->of($query)
            ->addIndexColumn()
            ->editColumn('agent_name', function ($row) use (&$unknownCounter, &$unknownMap) {
                if ($row->agent_name === null) {
                    if (!isset($unknownMap[$row->user_id])) {
                        $unknownMap[$row->user_id] = "Unknown User {$unknownCounter}";
                        $unknownCounter++;
                    }
                    return $row->user_id;
                }
                return $row->agent_name;
            })
            ->editColumn('total_working_hours', function ($row) {
                $hours = $row->total_working_hours;
                $minutes = $hours * 60;
                return number_format($hours, 0) . " (" . number_format($minutes, 0) . " mins)";
            })
            ->editColumn('active_calling_time', function ($row) {
                return $this->formatHoursMinsSecs($row->active_calling_seconds);
            })
            ->editColumn('idle_time', function ($row) {
                return $this->formatHoursMinsSecs($row->idle_seconds);
            })
            ->editColumn('calls_per_hour', function ($row) {
                return number_format($row->calls_per_hour, 2);
            });
    }

    public function query()
    {
        $start = request()->get('start_date') ?? now()->startOfMonth()->format('Y-m-d');
        $end = request()->get('end_date') ?? now()->endOfDay()->format('Y-m-d');
        $diffInDays = (strtotime($end) - strtotime($start)) / (60 * 60 * 24) + 1;
        $workingHoursPerDay = 6;
        $totalWorkingHours = $diffInDays * $workingHoursPerDay;

        // Get all unique user_ids in lead_calls for the period
        $userIds = LeadCall::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->pluck('user_id')
            ->unique();

        $collection = collect();
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            $agentName = $user ? $user->name : null;

            $calls = LeadCall::where('user_id', $userId)
                ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->get();

            $totalCallDurationSec = $calls->sum('duration');
            $activeCallingSeconds = $totalCallDurationSec;
            $idleSeconds = max(0, ($totalWorkingHours * 3600) - $totalCallDurationSec);
            $callsPerHour = $totalWorkingHours > 0 ? $calls->count() / $totalWorkingHours : 0;

            $collection->push((object) [
                'user_id' => $userId,
                'agent_name' => $agentName,
                'total_working_hours' => $totalWorkingHours,
                'active_calling_seconds' => $activeCallingSeconds,
                'idle_seconds' => $idleSeconds,
                'calls_per_hour' => round($callsPerHour, 2),
            ]);
        }

        return $collection;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('lead-calls-table')
            ->columns([
                ['data' => 'DT_RowIndex', 'title' => '#', 'orderable' => false, 'searchable' => false],
                ['data' => 'agent_name', 'title' => 'Agent Name'],
                ['data' => 'total_working_hours', 'title' => 'Total Working Hours'],
                ['data' => 'active_calling_time', 'title' => 'Active Calling Time'],
                ['data' => 'idle_time', 'title' => 'Idle Time'],
                ['data' => 'calls_per_hour', 'title' => 'Calls per Hour'],
            ])
            ->minifiedAjax()
            ->parameters([
                'responsive' => true,
                'processing' => true,
                'serverSide' => true,
                'autoWidth' => false,
                'pageLength' => 25,
                'order' => [[1, 'asc']],
            ]);
    }

    private function formatHoursMinsSecs($seconds)
    {
        $seconds = (int) $seconds;
        $hours = $seconds / 3600;
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return number_format($hours, 2) . " (" . $minutes . " min " . $secs . " sec)";
    }

    protected function filename(): string
    {
        return 'UserProductivityReport_' . date('YmdHis');
    }
}

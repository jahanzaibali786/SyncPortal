<?php

namespace App\DataTables;

use App\Models\LeadCall;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;

class UserPerformanceDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->queryBuilder($query)
            ->addColumn('conversion_rate', function ($row) {
                if ($row->total_leads == 0)
                    return '0%';
                return round(($row->converted_leads / $row->total_leads) * 100, 2) . '%';
            });
    }

    public function query()
    {
        $start = request()->get('start_date') ?? now()->startOfMonth()->format('Y-m-d');
        $end = request()->get('end_date') ?? now()->endOfDay()->format('Y-m-d');

        return LeadCall::select([
            'users.name as agent_name',
            DB::raw('COUNT(lead_calls.id) as total_calls'),
            DB::raw("SUM(CASE WHEN lead_calls.status = 'answered' THEN 1 ELSE 0 END) as answered_calls"),
            DB::raw('COUNT(DISTINCT lead_calls.lead_id) as total_leads'),
            DB::raw('AVG(lead_calls.duration) as avg_duration'),
            DB::raw("SUM(CASE WHEN lead_calls.call_result = 'converted' THEN 1 ELSE 0 END) as converted_leads"),
        ])
            ->join('users', 'users.id', '=', 'lead_calls.user_id')
            ->whereBetween('lead_calls.created_at', [$start, $end])
            ->groupBy('users.id', 'users.name');
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
                'order' => [[0, 'desc']],
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('agent_name')->title('Agent Name'),
            Column::computed('total_calls')->title('Total Calls Made'),
            Column::computed('answered_calls')->title('Answered Calls'),
            Column::computed('total_leads')->title('Total Leads'),
            Column::computed('avg_duration')->title('Average Call Duration (sec)'),
            Column::computed('converted_leads')->title('Converted Leads'),
            Column::computed('conversion_rate')->title('Conversion Rate (%)'),
        ];
    }
}

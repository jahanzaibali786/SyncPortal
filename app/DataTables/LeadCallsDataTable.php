<?php

namespace App\DataTables;

use App\Models\LeadCall;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;

class LeadCallsDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('lead_name', fn($row) => $row->lead->company_name ?? 'N/A')
            ->addColumn('agent_name', fn($row) => $row->user->name ?? 'N/A')
            ->addColumn('number', fn($row) => $row->to_number)
            ->addColumn('status', fn($row) => $row->status)
            ->addColumn('recording_available', function ($row) {
                if ($row->recording) {
                    return '<a href="' . asset('call_recordings/' . $row->recording) . '" target="_blank">Play</a>';
                }
                return 'No';
            })
            ->rawColumns(['recording_available']);
    }

    public function query(LeadCall $model)
    {
        $start = request()->get('start_date') ?? now()->startOfMonth()->format('Y-m-d');
        $end = request()->get('end_date') ?? now()->endOfDay()->format('Y-m-d');
        $status = request()->get('status'); // 👈 get dropdown value

        $query = $model->newQuery()
            ->with(['lead', 'user'])
            ->whereBetween('created_at', [$start, $end]);

        // 👇 Apply status filter if selected
        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query;
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
            Column::make('id')->title('#'),
            Column::computed('lead_name')->title('Lead Name'),
            Column::computed('agent_name')->title('Agent Name'),
            Column::computed('number')->title('Number'),
            Column::computed('status')->title('Status'),
            Column::computed('recording_available')->title('Recording Available'),
        ];
    }
}

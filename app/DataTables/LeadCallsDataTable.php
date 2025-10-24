<?php

namespace App\DataTables;

use App\Models\LeadCall;
use Storage;
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
            ->addColumn('status', function($row){
                if($row->status == '200'){
                    return '<span class="badge badge-success">Answered</span>';
                }
                //408
                else if($row->status == '408'){
                    return '<span class="badge badge-danger">No Answer</span>';
                }
                //486
                else if($row->status == '486'){
                    return '<span class="badge badge-warning">Busy</span>';
                }
                //503
                else if($row->status == '503'){
                    return '<span class="badge badge-dark">Power Off</span>';
                }
                else{
                    return $row->status ;
                }
            })
            ->addColumn('recording_available', function ($row) {
                if ($row->recording) {
                    $audioUrl = Storage::disk('recordings')->url($row->recording);
                    return '<audio controls style="width: 180px;">
                            <source src="' . $audioUrl . '" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>';
                }
                return 'No Recording';
            })
            ->rawColumns(['status','recording_available']);
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

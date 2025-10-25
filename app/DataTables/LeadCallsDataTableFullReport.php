<?php

namespace App\DataTables;

use App\Models\LeadCall;
use App\Models\Lead;
use App\Models\User;
use Storage;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;

class LeadCallsDataTableFullReport extends DataTable
{
    public function dataTable($query)
    {
        $unknownLeadCounter = 1;
        $unknownUserCounter = 1;
        $unknownLeadMap = [];
        $unknownUserMap = [];

        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('subject', function ($row) use (&$unknownLeadCounter, &$unknownLeadMap) {
                // If lead_id is missing in leads table, assign "Unknown Lead X"
                $lead = $row->lead ?? Lead::find($row->lead_id);
                if (!$lead) {
                    if (!isset($unknownLeadMap[$row->lead_id])) {
                        $unknownLeadMap[$row->lead_id] = "Unknown Lead {$unknownLeadCounter}";
                        $unknownLeadCounter++;
                    }
                    return $unknownLeadMap[$row->lead_id];
                }
                // Prefer lead name or fallback to subject
                return $lead->company_name ?? $row->subject ?? 'N/A';
            })
            ->editColumn('call_type', function ($row) {
                return ucfirst($row->call_type ?? 'Unknown');
            })
            ->editColumn('start', function ($row) {
                return $row->start ? Carbon::parse($row->start)->format('h:i A') : 'N/A';
            })
            ->editColumn('end', function ($row) {
                return $row->end ? Carbon::parse($row->end)->format('h:i A') : 'N/A';
            })
            ->editColumn('duration', function ($row) {
                return $this->formatDuration($row->duration);
            })
            ->editColumn('type', function ($row) {
                return $row->type ?? 'N/A';
            })
           ->editColumn('recording', function ($row) {
                if ($row->recording) {
                    $audioUrl = Storage::disk('recordings')->url($row->recording);
                    return '<audio controls style="width: 180px;">
                                <source src="' . $audioUrl . '" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>';
                }
                return 'No Recording';
            })
            ->editColumn('user_id', function ($row) use (&$unknownUserCounter, &$unknownUserMap) {
                $user = $row->user ?? User::find($row->user_id);
                if (!$user) {
                    if (!isset($unknownUserMap[$row->user_id])) {
                        $unknownUserMap[$row->user_id] = "Unknown User {$unknownUserCounter}";
                        $unknownUserCounter++;
                    }
                    return $unknownUserMap[$row->user_id];
                }
                return $user->name ?? 'N/A';
            })
            ->rawColumns(['recording']);
    }

    public function query(LeadCall $model)
    {
        $start = request()->get('start_date') ?? now()->startOfMonth()->format('Y-m-d');
        $end = request()->get('end_date') ?? now()->endOfDay()->format('Y-m-d');
        $status = request()->get('status');

        $query = $model->newQuery()
            ->with(['lead', 'user'])
            ->whereBetween('created_at', [$start, $end]);

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
            Column::computed('DT_RowIndex')->title('#')->orderable(false)->searchable(false),
            Column::make('user_id')->title('User'),
            Column::make('subject')->title('Subject'),
            Column::make('call_type')->title('Call Type'),
            Column::make('start')->title('Start'),
            Column::make('end')->title('End'),
            Column::make('duration')->title('Duration'),
            Column::make('type')->title('Type'),
            Column::computed('recording')->title('Recording')->exportable(false)->printable(false),
        ];
    }

    /**
     * Convert seconds into readable minutes & seconds (e.g. 41 min 56 sec)
     */
    private function formatDuration($seconds)
    {
        $seconds = (int) $seconds;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return "{$minutes} min {$remainingSeconds} sec";
    }

    protected function filename(): string
    {
        return 'CallsReport_' . date('YmdHis');
    }
}

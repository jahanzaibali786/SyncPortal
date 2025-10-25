<?php

namespace App\DataTables;

use App\Models\LeadCall;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;
use Storage;

class CallDateDataTable extends DataTable
{
    public function dataTable($query)
    {
        // 🔍 Detect if this request is for modal (detail) view
        $isDetailsRequest = request()->has('details') && request()->get('details') == 1;

        if ($isDetailsRequest) {
            return datatables()
                ->of($query->get()->makeHidden(['user_id', 'type']))
                ->addIndexColumn()
                ->editColumn('duration', function ($row) {
                    $sec = (int) $row->duration;
                    $min = floor($sec / 60);
                    $rem = $sec % 60;
                    return "{$min}m {$rem}s";
                })
                ->addColumn('user', fn($row) => optional($row->user)->name)
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

                ->rawColumns(['recording']);
        }


        // ✅ Summary (main) table
        return datatables()
            ->of($query)
            ->addIndexColumn()
            ->editColumn('date', fn($row) => Carbon::parse($row->date)->format('d M, Y'))
            ->editColumn('total_calls', function ($row) {
                return '<a href="#" class="show-call-details" data-date="' . $row->date . '" data-type="total">' . $row->total_calls . '</a>';
            })
            ->editColumn('answered_calls', function ($row) {
                return '<a href="#" class="show-call-details" data-date="' . $row->date . '" data-type="answered">' . $row->answered_calls . '</a>';
            })
            ->editColumn('unanswered_calls', function ($row) {
                return '<a href="#" class="show-call-details" data-date="' . $row->date . '" data-type="unanswered">' . $row->unanswered_calls . '</a>';
            })
            ->editColumn('avg_duration', fn($row) => $this->formatDuration($row->avg_duration))
            ->rawColumns(['total_calls', 'answered_calls', 'unanswered_calls']);
    }

    public function query(LeadCall $model)
    {
        // 🔍 Check if detail mode
        $isDetailsRequest = request()->has('details') && request()->get('details') == 1;

        if ($isDetailsRequest) {
            $date = request()->get('date');
            $type = request()->get('type');

            $q = $model->newQuery()
                ->whereDate('created_at', $date)
                ->with('user:id,name');

            if ($type === 'answered') {
                $q->where('status', '200');
            } elseif ($type === 'unanswered') {
                $q->where('status', '!=', '200');
            }

            // ✅ Select only the fields we actually need
            return $q->select([
                'subject',
                'call_type',
                'to_number as number',
                'start as start',
                'end as end',
                'duration',
                'call_type as type',
                'recording as recording',
                'user_id',
            ]);
        }

        // ✅ Summary query
        $start = request()->get('start_date') ?? now()->startOfMonth()->format('Y-m-d');
        $end = request()->get('end_date') ?? now()->endOfDay()->format('Y-m-d');
        $status = request()->get('status');

        $query = $model->newQuery()
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_calls'),
                DB::raw("SUM(CASE WHEN status = '200' THEN 1 ELSE 0 END) as answered_calls"),
                DB::raw("SUM(CASE WHEN status != '200' THEN 1 ELSE 0 END) as unanswered_calls"),
                DB::raw('AVG(duration) as avg_duration'),
            ])
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc');

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
                'order' => [[1, 'desc']],
            ]);
    }

    protected function getColumns()
    {
        $isDetailsRequest = request()->has('details') && request()->get('details') == 1;

        if ($isDetailsRequest) {
            // ✅ Columns for detail modal view
            return [
                Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false),
                Column::make('subject')->title('Subject'),
                Column::make('call_type')->title('Call Type'),
                Column::make('number')->title('Number'),
                Column::make('start')->title('Start'),
                Column::make('end')->title('End'),
                Column::make('duration')->title('Duration'),
                Column::make('type')->title('Type'),
                Column::make('recording')->title('Recording'),
                Column::make('user')->title('User'),
            ];
        }

        // ✅ Columns for summary table
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false),
            Column::make('date')->title('Date'),
            Column::make('total_calls')->title('Total Calls Made'),
            Column::make('answered_calls')->title('Answered Calls'),
            Column::make('unanswered_calls')->title('Unanswered Calls'),
            Column::make('avg_duration')->title('Average Call Duration'),
        ];
    }

    private function formatDuration($seconds)
    {
        if (!$seconds || $seconds <= 0) {
            return '0 sec';
        }

        $seconds = (int) round($seconds);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d hr %d min %d sec', $hours, $minutes, $remainingSeconds);
        }

        return sprintf('%d min %d sec', $minutes, $remainingSeconds);
    }
}

<?php

namespace App\DataTables;

use App\Models\LeadCall;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use Carbon\Carbon;

class CallDateDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addIndexColumn()
            ->editColumn('date', fn($row) => Carbon::parse($row->date)->format('d M, Y'))
            ->editColumn('total_calls', fn($row) => $row->total_calls)
            ->editColumn('answered_calls', fn($row) => $row->answered_calls)
            ->editColumn('unanswered_calls', fn($row) => $row->unanswered_calls)
            ->editColumn('avg_duration', function ($row) {
                return $this->formatDuration($row->avg_duration);
            });
    }

    public function query(LeadCall $model)
    {
        $start = request()->get('start_date') ?? now()->startOfMonth()->format('Y-m-d');
        $end = request()->get('end_date') ?? now()->endOfDay()->format('Y-m-d');
        $status = request()->get('status'); // dropdown filter

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
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false),
            Column::make('date')->title('Date'),
            Column::make('total_calls')->title('Total Calls Made'),
            Column::make('answered_calls')->title('Answered Calls'),
            Column::make('unanswered_calls')->title('Unanswered Calls'),
            Column::make('avg_duration')->title('Average Call Duration'),
        ];
    }

    /**
     * Format duration in seconds to human-readable form
     * Example: 14 sec → 0 min 14 sec
     * Example: 3665 sec → 1 hour 1 min 5 sec
     */
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
            return sprintf(
                '%d hour%s %d min %d sec',
                $hours,
                $hours > 1 ? 's' : '',
                $minutes,
                $remainingSeconds
            );
        }

        return sprintf('%d min %d sec', $minutes, $remainingSeconds);
    }

    protected function filename(): string
    {
        return 'CallDateReport_' . date('YmdHis');
    }
}

<?php

namespace App\DataTables;

use App\Models\JournalEntry;
use Carbon\Carbon;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GeneralJournalDataTable extends DataTable
{
    protected $startDate;
    protected $endDate;
    protected $columnCount = 9; // Total number of columns in the table

    public function __construct()
    {
        parent::__construct();

        $this->startDate = request('startDate') 
            ? Carbon::parse(request('startDate'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $this->endDate = request('endDate') 
            ? Carbon::parse(request('endDate'))->endOfDay()
            : Carbon::now()->endOfDay();
    }

    public function dataTable($query)
    {
        return datatables()
            ->collection($query)
            ->addColumn('trans_date', function ($row) {
                if ($row->is_header ?? false) {
                    // Create a full row spanning header for the date
                    return '<strong class="journal-header" colspan="' . $this->columnCount . '">' . e($row->date_display ?? '') . '</strong>';
                }
                if ($row->is_line ?? false || $row->is_spacer ?? false) {
                    return '';
                }
                return !empty($row->date) ? Carbon::parse($row->date)->format('m/d/Y') : '';
            })
            ->addColumn('trans_id', function ($row) {
                if ($row->is_header ?? false || $row->is_line ?? false || $row->is_spacer ?? false) {
                    return '';
                }
                return $row->journal_number ?? $row->id ?? '';
            })
            ->addColumn('trans_type', function ($row) {
                if ($row->is_header ?? false || $row->is_line ?? false || $row->is_spacer ?? false) {
                    return '';
                }
                return 'Journal Entry';
            })
            ->addColumn('num', function ($row) {
                if ($row->is_header ?? false || $row->is_line ?? false || $row->is_spacer ?? false) {
                    return '';
                }
                return $row->reference ?? '';
            })
            ->addColumn('name', function ($row) {
                if ($row->is_header ?? false || $row->is_line ?? false || $row->is_spacer ?? false) {
                    return '';
                }
                return $row->description ?? '';
            })
            ->addColumn('memo', function ($row) {
                if ($row->is_header ?? false) {
                    return '';
                }
                if ($row->is_line ?? false) {
                    return $row->memo ?? '';
                }
                return $row->memo ?? '';
            })
            ->addColumn('account', function ($row) {
                if ($row->is_header ?? false || $row->is_spacer ?? false) {
                    return '';
                }
                if ($row->is_line ?? false) {
                    return '&nbsp;&nbsp;&nbsp;&nbsp;' . e($row->account_name ?? 'Unknown Account');
                }
                return '';
            })
            ->addColumn('debit', function ($row) {
                if (!($row->is_line ?? false)) {
                    return '';
                }
                $debit = $row->debit ?? 0;
                return $debit > 0 ? number_format($debit, 2) : '';
            })
            ->addColumn('credit', function ($row) {
                if (!($row->is_line ?? false)) {
                    return '';
                }
                $credit = $row->credit ?? 0;
                return $credit > 0 ? number_format($credit, 2) : '';
            })
            ->addColumn('row_class', function ($row) {
                if ($row->is_header ?? false) {
                    return 'journal-header-row full-width-date';
                }
                if ($row->is_line ?? false) {
                    return 'journal-line-row';
                }
                if ($row->is_spacer ?? false) {
                    return 'journal-spacer-row';
                }
                return 'journal-entry-row';
            })
            ->rawColumns(['trans_date', 'account'])
            ->setRowAttr([
                'colspan' => function ($row) {
                    // Apply colspan attribute to date header rows
                    if ($row->is_header ?? false) {
                        return $this->columnCount;
                    }
                    return null;
                },
                'class' => function ($row) {
                    if ($row->is_header ?? false) {
                        return 'journal-header-row full-width-date';
                    }
                    if ($row->is_line ?? false) {
                        return 'journal-line-row';
                    }
                    if ($row->is_spacer ?? false) {
                        return 'journal-spacer-row';
                    }
                    return 'journal-entry-row';
                }
            ]);
    }

    public function query()
    {
        $journalEntries = JournalEntry::with(['lines.chartOfAccount'])
            ->where('company_id', company()->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $report = collect();
        $currentDate = null;

        foreach ($journalEntries as $entry) {
            $entryDate = Carbon::parse($entry->date)->format('Y-m-d');

            // Add date header if date changed
            if ($currentDate !== $entryDate) {
                $currentDate = $entryDate;
                $report->push((object)[
                    'date_display' => Carbon::parse($entry->date)->format('l, F j, Y'),
                    'is_header' => true
                ]);
            }

            // Add journal entry header
            $report->push((object)[
                'id' => $entry->id,
                'date' => $entry->date,
                'journal_number' => $entry->journal_number,
                'reference' => $entry->reference,
                'description' => $entry->description,
                'memo' => $entry->memo,
                'is_header' => false,
                'is_line' => false
            ]);

            // Add journal entry lines
            foreach ($entry->lines as $line) {
                $report->push((object)[
                    'journal_entry_id' => $entry->id,
                    'account_name' => $line->chartOfAccount->name ?? 'Unknown Account',
                    'account_code' => $line->chartOfAccount->code ?? '',
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'memo' => $line->memo,
                    'is_header' => false,
                    'is_line' => true
                ]);
            }

            // Add spacer row
            $report->push((object)[
                'is_spacer' => true,
                'is_header' => false,
                'is_line' => false
            ]);
        }

        return $report;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('general-journal-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->parameters([
                'paging' => false,
                'searching' => false,
                'info' => false,
                'ordering' => false,
                'createdRow' => "function(row, data, dataIndex) {
                    if (data.row_class) {
                        $(row).addClass(data.row_class);
                    }
                    
                    // If this is a header row, make it span all columns
                    if (data.row_class && data.row_class.includes('journal-header-row')) {
                        $('td:first', row).attr('colspan', " . $this->columnCount . ");
                        $('td:not(:first)', row).remove();
                    }
                }"
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('trans_date')->title('DATE')->width('10%'),
            Column::make('trans_id')->title('TRANS #')->width('8%'),
            Column::make('trans_type')->title('TYPE')->width('12%'),
            Column::make('num')->title('NUM')->width('8%'),
            Column::make('name')->title('NAME')->width('15%'),
            Column::make('memo')->title('MEMO/DESCRIPTION')->width('15%'),
            Column::make('account')->title('ACCOUNT')->width('20%'),
            Column::make('debit')->title('DEBIT')->width('8%')->addClass('text-right'),
            Column::make('credit')->title('CREDIT')->width('8%')->addClass('text-right'),
        ];
    }
}
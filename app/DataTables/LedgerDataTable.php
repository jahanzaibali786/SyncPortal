<?php

namespace App\DataTables;

use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;

class LedgerDataTable extends DataTable
{
    public $accountId1;

    /** Call this from the controller */
    public function setAccountId($accountId1): self
    {
        $this->accountId1 = $accountId1 ? $accountId1 : 'all';
        return $this;
    }

    public function dataTable($query)
    {
        $runningBalance = 0;
        $openingBalance = $this->getOpeningBalance();
        $accountType = $this->getAccountType();

        // Fetch actual entries
        $entries = $query->get();

        // Build Opening Balance row for A/L/E
        $openingRow = null;
        if (in_array($accountType, ['Asset', 'Liability', 'Equity'])) {
            $openingRow = (object) [
                'date' => request('startDate') ?? '',
                'voucher_no' => '',
                'account_name' => 'Opening Balance',
                'debit' => 0,
                'credit' => 0,
                'memo' => '',
                'running_balance' => $openingBalance,
            ];
        }

        // Merge opening row + entries
        $data = $openingRow ? collect([$openingRow])->merge($entries) : $entries;

        return datatables()
            ->collection($data)
            ->addColumn('date', fn($row) => $row->date ?? optional($row->journalEntry)->date)
            ->addColumn('voucher_no', fn($row) => $row->voucher_no ?? optional($row->journalEntry)->number)
            ->addColumn('account_name', fn($row) => $row->account_name ?? optional($row->chartOfAccount)->name)
            ->editColumn('debit', fn($row) => isset($row->debit) && $row->debit > 0 ? number_format($row->debit, 2) : '')
            ->editColumn('credit', fn($row) => isset($row->credit) && $row->credit > 0 ? number_format($row->credit, 2) : '')
            ->addColumn('running_balance', function ($row) use (&$runningBalance, $openingBalance, $accountType) {
                if (isset($row->account_name) && $row->account_name === 'Opening Balance') {
                    $runningBalance = $row->running_balance; // reset with opening
                    return number_format($runningBalance, 2);
                }
                $runningBalance += ($row->debit - $row->credit);
                return number_format($runningBalance, 2);
            });
    }


    public function query()
    {
        $query = JournalEntryLine::query()
            ->with([
                'chartOfAccount:id,name,chart_of_account_type_id,chart_of_account_sub_type_id',
                'chartOfAccount.accountType:id,name',
                'chartOfAccount.accountSubType:id,name', 
                'journalEntry:id,date,number,status,company_id'
            ])
            ->whereHas('journalEntry', function ($q) {
                $q->where('company_id', company()->id);
            });

        // 🔎 Filter by account
        if (request()->filled('account_id') && request('account_id') !== 'all') {
            $query->where('chart_of_account_id', request('account_id'));
        } elseif ($this->accountId1 !== 'all') {
            $query->where('chart_of_account_id', $this->accountId1);
        }

        // 🔎 Filter by date range
        if (request()->filled('startDate') && request()->filled('endDate')) {
            try {
                $start = Carbon::parse(request('startDate'))->startOfDay();
                $end = Carbon::parse(request('endDate'))->endOfDay();

                $query->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$start, $end]));
            } catch (\Exception $e) {
                // Ignore invalid dates
            }
        }

        return $query->select([
            'journal_entry_lines.id as line_id',
            'journal_entry_lines.journal_entry_id',
            'journal_entry_lines.chart_of_account_id',
            'journal_entry_lines.debit',
            'journal_entry_lines.credit',
            'journal_entry_lines.memo',
        ])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entry_lines.chart_of_account_id')
            ->join('chart_of_account_sub_types', 'chart_of_account_sub_types.id', '=', 'chart_of_accounts.chart_of_account_sub_type_id')
            ->join('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_account_sub_types.chart_of_account_type_id')
            ->orderBy('chart_of_account_types.name', 'asc')   // First by type
            ->orderBy('chart_of_account_sub_types.name', 'asc') // Then by subtype
            ->orderBy('chart_of_accounts.name', 'asc')        // Then by account
            ->orderBy('journal_entries.date', 'asc');         // Finally by entry date
    }


    /**
     * Calculate Opening Balance before startDate (only for Asset, Liability, Equity)
     */
    protected function getOpeningBalance(): float
    {
        if (!request()->filled('startDate')) {
            return 0;
        }

        try {
            $start = Carbon::parse(request('startDate'))->startOfDay();
        } catch (\Exception $e) {
            return 0;
        }

        $accountType = $this->getAccountType();
        if (!in_array($accountType, ['Asset', 'Liability', 'Equity'])) {
            return 0; // Income & Expense reset each year
        }

        $query = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.company_id', company()->id)
            ->where('journal_entries.date', '<', $start);

        if (request()->filled('account_id') && request('account_id') !== 'all') {
            $query->where('journal_entry_lines.chart_of_account_id', request('account_id'));
        } elseif ($this->accountId1 !== 'all') {
            $query->where('journal_entry_lines.chart_of_account_id', $this->accountId1);
        }

        $totals = $query->selectRaw("SUM(debit) as total_debit, SUM(credit) as total_credit")->first();

        return ($totals->total_debit ?? 0) - ($totals->total_credit ?? 0);
    }

    /**
     * Get the selected account type (via chart_of_account_types.name)
     */
    protected function getAccountType(): ?string
    {
        $accountId = request('account_id') !== 'all' ? request('account_id') : $this->accountId1;

        if ($accountId && $accountId !== 'all') {
            return optional(
                ChartOfAccount::with('accountType')->find($accountId)
            )->accountType->name;
        }

        return null;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('ledger-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc') // order by Date
            ->dom('Bfrtip')
            ->parameters([
                'paging' => false,
                'searching' => false,
                'info' => false,
                'ordering' => false,
                'scrollY' => '400px',
                'scrollCollapse' => true,
                'createdRow' => "function(row, data, dataIndex) {
                    if (data.DT_RowClass) {
                        $(row).addClass(data.DT_RowClass);
                    }
                    if (data.DT_RowData) {
                        for (let key in data.DT_RowData) {
                            $(row).attr('data-' + key, data.DT_RowData[key]);
                        }
                    }
                }"
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('date')->title('Date'),
            Column::make('voucher_no')->title('Voucher No'),
            Column::make('account_name')->title('Account'),
            Column::make('debit')->title('Debit'),
            Column::make('credit')->title('Credit'),
            Column::make('running_balance')->title('Running Balance'),
            Column::make('memo')->title('Memo'),
        ];
    }
}

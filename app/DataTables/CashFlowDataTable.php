<?php

namespace App\DataTables;

use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CashFlowDataTable extends DataTable
{
    protected $startDate;
    protected $endDate;

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
            ->addColumn('description', function ($row) {
                if ($row->is_section_header ?? false) {
                    return '<strong class="section-header">' . e($row->name) . '</strong>';
                }
                if ($row->is_subtotal ?? false) {
                    return '<strong class="subtotal-label">' . e($row->name) . '</strong>';
                }
                if ($row->is_total ?? false) {
                    return '<strong class="total-label">' . e($row->name) . '</strong>';
                }
                return ($row->is_child ?? false ? '&nbsp;&nbsp;&nbsp;&nbsp;' : '') . e($row->name);
            })
            ->addColumn('amount', function ($row) {
                $amount = $row->amount ?? 0;
                if ($row->is_section_header ?? false) return '';
                if ($row->is_subtotal ?? false) {
                    return '<strong class="subtotal-amount">' . number_format($amount, 2) . '</strong>';
                }
                if ($row->is_total ?? false) {
                    return '<strong class="total-amount">' . number_format($amount, 2) . '</strong>';
                }
                return $amount == 0 ? '' : '<span class="amount-cell">' . number_format($amount, 2) . '</span>';
            })
            ->addColumn('row_class', function ($row) {
                if ($row->is_section_header ?? false) return 'section-header-row';
                if ($row->is_subtotal ?? false) return 'subtotal-row';
                if ($row->is_total ?? false) return 'total-row';
                if ($row->is_child ?? false) return 'child-row';
                return '';
            })
            ->rawColumns(['description', 'amount']);
    }

    public function query()
    {
        // Get Net Income from P&L
        $netIncome = (float) $this->getNetIncome();

        $report = collect();

        $sections = [
            'operating' => 'CASH FLOWS FROM OPERATING ACTIVITIES',
            'investing' => 'CASH FLOWS FROM INVESTING ACTIVITIES',
            'financing' => 'CASH FLOWS FROM FINANCING ACTIVITIES',
        ];

        $netChange = 0;

        foreach ($sections as $key => $title) {
            $report->push((object)[
                'name' => $title,
                'amount' => 0,
                'is_section_header' => true
            ]);

            $items = $this->getCashFlowByCategory($key);

            if ($key === 'operating') {
                $report->push((object)[
                    'name' => 'Net Income',
                    'amount' => $netIncome,
                    'is_child' => true
                ]);
            }

            foreach ($items as $item) {
                $report->push($item);
            }

            // Special case: Add Owner’s Contribution inside Financing
            $ownerContribution = 0;
            if ($key === 'financing') {
                $ownerContribution = DB::table('journal_entry_lines')
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->join('chart_of_accounts', 'journal_entry_lines.chart_of_account_id', '=', 'chart_of_accounts.id')
                    ->where('journal_entries.company_id', company()->id)
                    ->whereBetween('journal_entries.date', [$this->startDate, $this->endDate])
                    ->where('journal_entries.status', 'draft')
                    ->where('chart_of_accounts.name', "Owner’s Equity")
                    ->select(DB::raw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as contribution'))
                    ->value('contribution');

                $report->push((object)[
                    'name' => "Owner’s Contribution",
                    'amount' => (float) ($ownerContribution ?? 0),
                    'is_child' => true
                ]);
            }

            // ✅ Corrected subtotal calculation
            $subtotal = $items->sum('amount');

            if ($key === 'operating') {
                $subtotal += $netIncome;
            }

            if ($key === 'financing') {
                $subtotal += (float) ($ownerContribution ?? 0);
            }

            $report->push((object)[
                'name' => 'Net Cash ' . ($key === 'operating'
                        ? 'Provided by Operating Activities'
                        : ($key === 'investing'
                            ? 'Used in Investing Activities'
                            : 'Used in Financing Activities')),
                'amount' => $subtotal,
                'is_subtotal' => true
            ]);

            $report->push((object)['name' => '', 'amount' => 0]); // spacing

            $netChange += $subtotal;
        }

        // Final Totals
        $report->push((object)[
            'name' => 'NET INCREASE (DECREASE) IN CASH',
            'amount' => $netChange,
            'is_total' => true
        ]);

        $beginningCash = 0; // can be pulled from opening balances if you want
        $endingCash = $beginningCash + $netChange;

        $report->push((object)[
            'name' => 'Cash at Beginning of Period',
            'amount' => $beginningCash,
            'is_total' => true
        ]);

        $report->push((object)[
            'name' => 'Cash at End of Period',
            'amount' => $endingCash,
            'is_total' => true
        ]);

        return $report;
    }

    private function getNetIncome()
    {
        $income = ChartOfAccount::where('chart_of_accounts.company_id', company()->id)
            ->leftJoin('chart_of_account_sub_types', 'chart_of_accounts.chart_of_account_sub_type_id', '=', 'chart_of_account_sub_types.id')
            ->leftJoin('chart_of_account_types', 'chart_of_account_sub_types.chart_of_account_type_id', '=', 'chart_of_account_types.id')
            ->leftJoin('journal_entry_lines', 'chart_of_accounts.id', '=', 'journal_entry_lines.chart_of_account_id')
            ->leftJoin('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.company_id', company()->id)
            ->whereBetween('journal_entries.date', [$this->startDate, $this->endDate])
            ->where('journal_entries.status', 'draft')
            ->whereIn('chart_of_account_types.name', ['income', 'expense'])
            ->select([DB::raw('SUM(CASE 
                    WHEN chart_of_account_types.name = "income" 
                        THEN journal_entry_lines.credit - journal_entry_lines.debit
                    ELSE journal_entry_lines.debit - journal_entry_lines.credit 
                END) as net_income')])
            ->first();

        return (float) ($income->net_income ?? 0);
    }

    private function getCashFlowByCategory($category)
    {
        $accounts = ChartOfAccount::where('chart_of_accounts.company_id', company()->id)
            ->join('chart_of_account_sub_types', 'chart_of_accounts.chart_of_account_sub_type_id', '=', 'chart_of_account_sub_types.id')
            ->where('chart_of_account_sub_types.cash_flow_category', $category)
            ->select('chart_of_accounts.id', 'chart_of_accounts.name', 'chart_of_accounts.parent_id')
            ->get();

        $items = collect();

        foreach ($accounts as $account) {
            $change = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.company_id', company()->id)
                ->whereBetween('journal_entries.date', [$this->startDate, $this->endDate])
                ->where('journal_entries.status', 'draft')
                ->where('journal_entry_lines.chart_of_account_id', $account->id)
                ->select(DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as change_amount'))
                ->value('change_amount');

                 $change = (float) ($change ?? 0);

                // ✅ Skip zero or empty entries
                if ($change == 0) {
                    continue;
                }

            $items->push((object)[
                'name' => $account->name,
                'amount' => (float) ($change ?? 0),
                'is_child' => !is_null($account->parent_id)
            ]);
        }

        return $items;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('cash-flow-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->parameters([
                'paging' => false,
                'searching' => false,
                'info' => false,
                'ordering' => false,
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('description')->title('')->width('70%'),
            Column::make('amount')->title('TOTAL')->width('30%')->addClass('text-right'),
        ];
    }
}

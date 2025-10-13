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
                if ($row->is_subtotal ?? false) return '<strong>' . number_format($amount, 2) . '</strong>';
                if ($row->is_total ?? false) return '<strong>' . number_format($amount, 2) . '</strong>';
                if ($amount == 0) return '';

                return number_format($amount, 2);
            })
            ->rawColumns(['description', 'amount']);
    }

    public function query()
    {
        $netIncome = (float) $this->getNetIncome();

        $operatingCF = $this->getCashFlowByCategory('operating');
        $investingCF = $this->getCashFlowByCategory('investing');
        $financingCF = $this->getCashFlowByCategory('financing');

        $netOperatingCF = $netIncome + $operatingCF->sum('amount');
        $netInvestingCF = $investingCF->sum('amount');
        $netFinancingCF = $financingCF->sum('amount');
        $netCashFlow = $netOperatingCF + $netInvestingCF + $netFinancingCF;

        $report = collect();

        // ---------------- OPERATING ----------------
        $report->push((object)[ 'name' => 'CASH FLOWS FROM OPERATING ACTIVITIES', 'amount' => 0, 'is_section_header' => true ]);
        $report->push((object)[ 'name' => 'Net Income', 'amount' => $netIncome, 'is_child' => true ]);
        foreach ($operatingCF as $item) $report->push($item);
        $report->push((object)[ 'name' => 'Net Cash Provided by Operating Activities', 'amount' => $netOperatingCF, 'is_subtotal' => true ]);
        $report->push((object)['name' => '', 'amount' => 0]); // spacing

        // ---------------- INVESTING ----------------
        $report->push((object)[ 'name' => 'CASH FLOWS FROM INVESTING ACTIVITIES', 'amount' => 0, 'is_section_header' => true ]);
        foreach ($investingCF as $item) $report->push($item);
        $report->push((object)[ 'name' => 'Net Cash Used in Investing Activities', 'amount' => $netInvestingCF, 'is_subtotal' => true ]);
        $report->push((object)['name' => '', 'amount' => 0]); // spacing

        // ---------------- FINANCING ----------------
        $report->push((object)[ 'name' => 'CASH FLOWS FROM FINANCING ACTIVITIES', 'amount' => 0, 'is_section_header' => true ]);
        foreach ($financingCF as $item) $report->push($item);
        $report->push((object)[ 'name' => 'Net Cash Used in Financing Activities', 'amount' => $netFinancingCF, 'is_subtotal' => true ]);
        $report->push((object)['name' => '', 'amount' => 0]); // spacing

        // ---------------- TOTAL ----------------
        $report->push((object)[ 'name' => 'NET INCREASE (DECREASE) IN CASH', 'amount' => $netCashFlow, 'is_total' => true ]);

        return $report;
    }

    private function getNetIncome()
    {
        $income = ChartOfAccount::where('chart_of_accounts.company_id', company()->id)
            ->leftJoin('chart_of_account_types', 'chart_of_accounts.chart_of_account_type_id', '=', 'chart_of_account_types.id')
            ->leftJoin('journal_entry_lines', 'chart_of_accounts.id', '=', 'journal_entry_lines.chart_of_account_id')
            ->leftJoin('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.company_id', company()->id)
            ->whereBetween('journal_entries.date', [$this->startDate, $this->endDate])
            ->where('journal_entries.status', 'draft')
            ->whereIn('chart_of_account_types.name', ['income', 'expense'])
            ->select([
                DB::raw('SUM(CASE WHEN chart_of_account_types.name = "income" 
                                  THEN journal_entry_lines.credit - journal_entry_lines.debit 
                                  ELSE journal_entry_lines.debit - journal_entry_lines.credit END) as net_income')
            ])
            ->first();

        return (float) ($income->net_income ?? 0);
    }

    private function getCashFlowByCategory($category)
    {
        $accounts = ChartOfAccount::query()
            ->where('company_id', company()->id)
            ->where('cash_flow_category', $category)
            ->select('id', 'name')
            ->get();

        $items = collect();

        foreach ($accounts as $acc) {
            $change = $this->getAccountChangeById($acc->id);
            if ($change == 0) continue;

            $items->push((object)[
                'name' => $acc->name,
                'amount' => $change,
                'is_child' => true
            ]);
        }

        return $items;
    }

    private function getAccountChangeById($accountId)
    {
        $account = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->where('je.company_id', company()->id)
            ->whereBetween('je.date', [$this->startDate, $this->endDate])
            ->where('je.status', '=', 'draft')
            ->where('jel.chart_of_account_id', $accountId)
            ->select(DB::raw('SUM(jel.debit - jel.credit) as change_amount'))
            ->first();

        return (float) ($account->change_amount ?? 0);
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

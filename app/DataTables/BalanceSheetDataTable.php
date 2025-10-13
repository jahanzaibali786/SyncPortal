<?php

namespace App\DataTables;

use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BalanceSheetDataTable extends DataTable
{
    protected $asOfDate;

    public function __construct()
    {
        parent::__construct();

        $this->asOfDate = request('asOfDate')
            ? Carbon::parse(request('asOfDate'))->endOfDay()
            : Carbon::now()->endOfDay();
    }

    public function dataTable($query)
    {
        return datatables()
            ->collection($query)
            ->addColumn('DT_RowClass', function ($row) {
                $classes = [];
                
                if (!empty($row->is_section_header)) {
                    $classes[] = 'section-header-row';
                    $classes[] = 'parent-row';
                    $classes[] = 'level-0';
                } elseif (!empty($row->is_subtotal)) {
                    $classes[] = 'subtotal-row';
                    $classes[] = 'child-row';
                    $classes[] = 'level-2';
                } elseif (!empty($row->is_total)) {
                    $classes[] = 'total-row';
                    $classes[] = 'level-0';
                } elseif (!empty($row->is_child)) {
                    $classes[] = 'account-detail';
                    $classes[] = 'child-row';
                    $classes[] = 'level-1';
                } else {
                    $classes[] = 'level-0';
                }
                
                if (!empty($row->parent_id)) {
                    $classes[] = 'parent-' . $row->parent_id;
                }
                
                return implode(' ', $classes);
            })
            ->addColumn('DT_RowData', function ($row) {
                $data = [];
                
                if (!empty($row->parent_id)) {
                    $data['parent'] = $row->parent_id;
                }
                
                if (!empty($row->has_children)) {
                    $data['has-children'] = 'true';
                }
                
                $data['level'] = $this->getRowLevel($row);
                $data['row-id'] = $row->id ?? 'row-' . uniqid();
                
                return $data;
            })
            ->addColumn('account_name', function ($row) {
                $indent = $this->getIndentation($row);
                
                // Section header with toggle
                if ($row->is_section_header) {
                    return $indent . '<span class="toggle-btn collapsed" data-target="' . $row->id . '">
                        <i class="fa fa-chevron-right toggle-icon"></i>
                    </span>
                    <strong class="section-header">' . e($row->name) . '</strong>';
                }
                
                // Total row
                if ($row->is_total) {
                    return '<strong class="total-label">' . e($row->name) . '</strong>';
                }
                
                // Subtotal row
                if ($row->is_subtotal) {
                    return '<strong class="subtotal-label">' . e($row->name) . '</strong>';
                }
                
                // Child account row
                if ($row->is_child) {
                    return $indent . '<span class="account-name">' . e($row->name) . '</span>';
                }
                
                // Empty row
                return '';
            })
            ->addColumn('amount', function ($row) {
                if ($row->is_section_header) {
                    // Show section totals
                    $total = $row->section_total ?? 0;
                    return $total != 0 ? '<strong class="section-total">' . number_format(abs($total), 2) . '</strong>' : '';
                }
                
                if ($row->is_total) {
                    return '<strong class="total-amount">' . number_format(abs($row->net), 2) . '</strong>';
                }
                
                if ($row->is_subtotal) {
                    return '<strong class="subtotal-amount">' . number_format(abs($row->net), 2) . '</strong>';
                }
                
                if ($row->amount == 0 && !$row->is_child) {
                    return '';
                }
                
                return $row->amount == 0 ? '' : '<span class="amount-cell">' . number_format(abs($row->amount), 2) . '</span>';
            })
            ->rawColumns(['account_name', 'amount']);
    }

    private function getRowLevel($row)
    {
        if (!empty($row->is_section_header) || !empty($row->is_total)) {
            return 0;
        } elseif (!empty($row->is_subtotal)) {
            return 2;
        } elseif (!empty($row->is_child)) {
            return 1;
        } else {
            return 0;
        }
    }

    private function getIndentation($row)
    {
        $level = $this->getRowLevel($row);
        return str_repeat('<span class="indent-spacer"></span>', $level);
    }

    public function query()
    {
        $accounts = ChartOfAccount::where('chart_of_accounts.company_id', company()->id)
            ->leftJoin('chart_of_account_types', 'chart_of_accounts.chart_of_account_type_id', '=', 'chart_of_account_types.id')
            ->leftJoin('journal_entry_lines', 'chart_of_accounts.id', '=', 'journal_entry_lines.chart_of_account_id')
            ->leftJoin('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.company_id', company()->id)
            ->where('journal_entries.date', '<=', $this->asOfDate)
            ->where('journal_entries.status', 'draft') // use approved in production
            ->select([
                'chart_of_accounts.id',
                'chart_of_accounts.name',
                'chart_of_account_types.name as account_type',
                DB::raw('COALESCE(SUM(journal_entry_lines.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_entry_lines.credit), 0) as total_credit'),
            ])
            ->whereIn('chart_of_account_types.name', ['Asset', 'Liability', 'Equity'])
            ->groupBy('chart_of_accounts.id', 'chart_of_accounts.name', 'chart_of_account_types.name')
            ->orderBy('chart_of_account_types.name')
            ->orderBy('chart_of_accounts.name')
            ->get();

        return $this->buildHierarchicalBalanceSheet($accounts);
    }

    private function buildHierarchicalBalanceSheet($accounts)
    {
        $report = collect();

        $emptyRow = function ($name = '', $amount = 0, $net = 0, $flags = []) {
            return (object) array_merge([
                'id' => 'row-' . uniqid(),
                'parent_id' => null,
                'name' => $name,
                'amount' => $amount,
                'net' => $net,
                'is_section_header' => false,
                'is_subtotal' => false,
                'is_total' => false,
                'is_child' => false,
                'has_children' => false,
            ], $flags);
        };

        // ---------- Assets ----------
        $assetAccounts = $accounts->where('account_type', 'Asset')->map(function ($acc) {
            $amount = $acc->total_debit - $acc->total_credit;
            return (object) [
                'id' => 'asset-acc-' . $acc->id,
                'parent_id' => 'assets-section',
                'name' => $acc->name,
                'amount' => $amount,
                'net' => $amount,
                'is_child' => true,
                'is_section_header' => false,
                'is_total' => false,
                'is_subtotal' => false,
                'has_children' => false,
            ];
        });
        $totalAssets = $assetAccounts->sum(fn($acc) => $acc->amount);

        // Add Assets section
        $report->push($emptyRow('ASSETS', 0, 0, [
            'id' => 'assets-section',
            'is_section_header' => true,
            'has_children' => true,
            'section_total' => $totalAssets
        ]));
        $report = $report->merge($assetAccounts);
        $report->push($emptyRow('Total Assets', 0, $totalAssets, [
            'id' => 'assets-subtotal',
            'parent_id' => 'assets-section',
            'is_subtotal' => true
        ]));
        $report->push($emptyRow('', 0, 0)); // Empty row

        // ---------- Liabilities ----------
        $liabilityAccounts = $accounts->where('account_type', 'Liability')->map(function ($acc) {
            $amount = $acc->total_credit - $acc->total_debit;
            return (object) [
                'id' => 'liability-acc-' . $acc->id,
                'parent_id' => 'liabilities-section',
                'name' => $acc->name,
                'amount' => $amount,
                'net' => $amount,
                'is_child' => true,
                'is_section_header' => false,
                'is_total' => false,
                'is_subtotal' => false,
                'has_children' => false,
            ];
        });
        $totalLiabilities = $liabilityAccounts->sum(fn($acc) => $acc->amount);

        // Add Liabilities section
        $report->push($emptyRow('LIABILITIES', 0, 0, [
            'id' => 'liabilities-section',
            'is_section_header' => true,
            'has_children' => true,
            'section_total' => $totalLiabilities
        ]));
        $report = $report->merge($liabilityAccounts);
        $report->push($emptyRow('Total Liabilities', 0, $totalLiabilities, [
            'id' => 'liabilities-subtotal',
            'parent_id' => 'liabilities-section',
            'is_subtotal' => true
        ]));
        $report->push($emptyRow('', 0, 0)); // Empty row

        // ---------- Equity ----------
        $equityAccounts = $accounts->where('account_type', 'Equity')->map(function ($acc) {
            $amount = $acc->total_credit - $acc->total_debit;
            return (object) [
                'id' => 'equity-acc-' . $acc->id,
                'parent_id' => 'equity-section',
                'name' => $acc->name,
                'amount' => $amount,
                'net' => $amount,
                'is_child' => true,
                'is_section_header' => false,
                'is_total' => false,
                'is_subtotal' => false,
                'has_children' => false,
            ];
        });
        $totalEquity = $equityAccounts->sum(fn($acc) => $acc->amount);

        // Add Equity section
        $report->push($emptyRow('EQUITY', 0, 0, [
            'id' => 'equity-section',
            'is_section_header' => true,
            'has_children' => true,
            'section_total' => $totalEquity
        ]));
        $report = $report->merge($equityAccounts);
        $report->push($emptyRow('Total Equity', 0, $totalEquity, [
            'id' => 'equity-subtotal',
            'parent_id' => 'equity-section',
            'is_subtotal' => true
        ]));

        // --- Get Net Profit from P&L ---
        $netProfit = $this->calculateNetProfit();

        // --- Add accumulated profit/loss row ---
        $report->push($emptyRow('Accumulated (Loss) / Profit', 0, $netProfit, [
            'id' => 'net-profit',
            'class' => 'net-profit',
            'is_subtotal' => true
        ]));

        $report->push($emptyRow('', 0, 0)); // Empty row

        // --- Final Total Liabilities & Equity ---
        $report->push($emptyRow(
            'TOTAL LIABILITIES & EQUITY',
            0,
            $totalLiabilities + $totalEquity + $netProfit,
            [
                'id' => 'grand-total',
                'is_total' => true
            ]
        ));

        return $report;
    }

    private function calculateNetProfit()
    {
        return DB::table('journal_entry_lines')
            ->join('chart_of_accounts', 'journal_entry_lines.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->join('chart_of_account_types', 'chart_of_accounts.chart_of_account_type_id', '=', 'chart_of_account_types.id')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.company_id', company()->id)
            ->where('journal_entries.date', '<=', $this->asOfDate)
            ->where('journal_entries.status', 'draft') // use approved entries
            ->whereIn('chart_of_account_types.name', ['Income', 'Expense'])
            ->selectRaw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as net_profit')
            ->value('net_profit') ?? 0;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('balance-sheet-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
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
            Column::make('account_name')->title('Accounts')->width('70%'),
            Column::make('amount')->title('Total')->width('30%')->addClass('text-right'),
        ];
    }
}
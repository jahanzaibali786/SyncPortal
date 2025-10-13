<?php

namespace App\DataTables;

use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ChartOfAccountsDataTable extends BaseDataTable
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();

        return $datatables
            ->editColumn('main_type', function ($row) {
                return $row->accountType?->name ?? '--';
            })
            ->editColumn('sub_type', function ($row) {
                return $row->accountSubType?->name ?? '--';
            })
            ->editColumn('status', function ($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-outline-secondary btn-sm edit-account" data-id="' . $row->id . '" data-name="' . $row->name . '" data-code="' . $row->code . '">
                    <i class="fa fa-edit"></i>
                </button>';
            })
            ->rawColumns(['status', 'action'])
            ->setRowId(fn($row) => 'row-' . $row->id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return ChartOfAccount::with(['accountType', 'accountSubType'])
            ->select('chart_of_accounts.*')
            ->where('company_id', company()->id);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('chartofaccounts-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->parameters([
                'paging' => false,
                'searching' => false,
                'info' => false,
                'ordering' => false,
                'scrollY' => '600px',
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

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => [
                'data' => 'DT_RowIndex',
                'orderable' => false,
                'searchable' => false,
                'title' => '#'
            ],
            'id' => ['data' => 'id', 'name' => 'id', 'title' => 'ID', 'visible' => showId()],
            'code' => ['data' => 'code', 'name' => 'code', 'title' => 'Code'],
            'name' => ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
            'main_type' => ['data' => 'main_type', 'name' => 'accountType.name', 'title' => 'Main Type'],
            'sub_type' => ['data' => 'sub_type', 'name' => 'accountSubType.name', 'title' => 'Sub Type'],
            'description' => ['data' => 'description', 'name' => 'description', 'title' => 'Description'],
            'status' => ['data' => 'status', 'name' => 'is_active', 'title' => 'Status'],
            'action' => [
                'data' => 'action',
                'name' => 'action',
                'title' => 'Action',
                'orderable' => false,
                'searchable' => false
            ],
        ];
    }
}
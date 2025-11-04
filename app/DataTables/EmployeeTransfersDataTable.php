<?php

namespace App\DataTables;

use App\Models\EmployeeTransfer;
use Carbon\Carbon;
use Yajra\DataTables\Html\Column;

class EmployeeTransfersDataTable extends BaseDataTable
{
    public function __construct() { parent::__construct(); }

    public function dataTable($query)
    {
        $dt = datatables()->eloquent($query);

        // avatar + name component (expects ['user' => User])
        $dt->addColumn('employee', fn($r) =>
            view('components.employee', ['user' => $r->employee])->render()
        );

        $dt->editColumn('transfer_date', fn($r) =>
            $r->transfer_date ? Carbon::parse($r->transfer_date)->format('Y-m-d') : null
        );

        $dt->addColumn('from_department_label', fn($r) => $r->fromDepartment->team_name ?? '--');
        $dt->addColumn('to_department_label',   fn($r) => $r->toDepartment->team_name   ?? '--');

        $dt->addColumn('action', fn($r) =>
            view('employees.transfers._actions', ['row' => $r])->render()
        );

        return $dt->rawColumns(['employee','action']);
    }

    public function query(EmployeeTransfer $model)
    {
        $q = $model->newQuery()
            ->with([
                'employee:id,name,image',
                'fromDepartment:id,team_name',
                'toDepartment:id,team_name',
            ]);

        // simple filters — mirror your exits table style
        if (request('employee') && request('employee') !== 'all') {
            $q->where('employee_id', request('employee'));
        }
        if ($s = request('searchText')) {
            $q->whereHas('employee', function ($qq) use ($s) {
                $qq->where('name', 'like', "%$s%")
                   ->orWhere('email', 'like', "%$s%");
            });
        }
        if ($fd = request('from_department_id') and $fd !== 'all') {
            $q->where('from_department_id', $fd);
        }
        if ($td = request('to_department_id') and $td !== 'all') {
            $q->where('to_department_id', $td);
        }

        return $q->select('*');
    }

    public function html()
    {
        return $this->setBuilder('employee-transfers-table')
            ->columns($this->getColumns())
            ->parameters([
                'initComplete' => 'function(){ $(".select-picker").selectpicker(); }',
            ]);
    }

    protected function getColumns()
    {
        $cols = [
            __('app.id')        => ['data'=>'id','name'=>'id','visible'=>false],
            __('app.employee')  => ['data'=>'employee','name'=>'employee','orderable'=>false,'searchable'=>false],
            __('app.from')      => ['data'=>'from_department_label','name'=>'from_department_id',],
            __('app.to')        => ['data'=>'to_department_label','name'=>'to_department_id',],
            __('app.date')      => ['data'=>'transfer_date','name'=>'transfer_date'],
            __('app.description') => ['data'=>'description','name'=>'description','visible'=>false],
        ];

        $action = Column::computed('action', __('app.action'))
            ->exportable(false)->printable(false)->orderable(false)->searchable(false)
            ->addClass('text-right pr-20');

        return array_merge($cols, [$action]);
    }
}

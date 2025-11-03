<?php

// app/DataTables/EmployeeExitsDataTable.php
namespace App\DataTables;

use App\Models\EmployeeExit;
use Carbon\Carbon;
use Yajra\DataTables\Html\Column;

class EmployeeExitsDataTable extends BaseDataTable
{
    public function __construct(private string $kind) { parent::__construct(); }

    public function dataTable($query)
    {
        $dt = datatables()->eloquent($query);

        $dt->addColumn('employee', fn($r) => view('components.employee', ['user'=>$r->employee])->render());
        $dt->editColumn('notice_date', fn($r) => $r->notice_date ? Carbon::parse($r->notice_date)->format('Y-m-d') : null);
        $dt->editColumn('effective_date', fn($r) => $r->effective_date ? Carbon::parse($r->effective_date)->format('Y-m-d') : null);
        $dt->addColumn('termination_type_label', fn($r) => $r->termination_type_label ?? '--');
        $dt->addColumn('action', fn($r) => view('employees.exits._actions', ['row'=>$r, 'kind'=>$this->kind])->render());

        $dt->rawColumns(['employee','action']);
        return $dt;
    }

    public function query(EmployeeExit $model)
    {
        $q = $model->newQuery()
            ->with(['employee:id,name,image'])
            ->where('kind', $this->kind);

        // simple filters (you can extend like your EmployeesDataTable)
        if (request('employee') && request('employee') !== 'all') {
            $q->where('employee_id', request('employee'));
        }
        if ($s = request('searchText')) {
            $q->whereHas('employee', fn($qq)=>$qq->where('name','like',"%$s%")->orWhere('email','like',"%$s%"));
        }

        return $q->select('*');
    }

    public function html()
    {
        return $this->setBuilder('employee-exits-table')
            ->columns($this->getColumns())
            ->parameters([
                'initComplete' => 'function(){ $(".select-picker").selectpicker(); }',
            ]);
    }

    protected function getColumns()
    {
        $cols = [
            __('app.id')         => ['data'=>'id','name'=>'id','visible'=>false],
            __('app.employee')   => ['data'=>'employee','name'=>'employee','orderable'=>false,'searchable'=>false],
            __('app.noticeDate') => ['data'=>'notice_date','name'=>'notice_date'],
            ($this->kind==='termination'?__('app.terminationDate'):__('app.resignationDate'))
                                => ['data'=>'effective_date','name'=>'effective_date'],
            __('app.description')=> ['data'=>'description','name'=>'description','visible'=>false],
        ];
        if ($this->kind==='termination') {
            $cols[__('app.type')] = ['data'=>'termination_type_label','name'=>'termination_type'];
        }

        $action = Column::computed('action', __('app.action'))
            ->exportable(false)->printable(false)->orderable(false)->searchable(false)
            ->addClass('text-right pr-20');

        return array_merge($cols, [$action]);
    }
}

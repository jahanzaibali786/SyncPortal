<?php

namespace App\DataTables;

use App\Models\Trip;
use Carbon\Carbon;
use Yajra\DataTables\Html\Column;

class TripsDataTable extends BaseDataTable
{
    public function __construct() { parent::__construct(); }

    public function dataTable($query)
    {
        $dt = datatables()->eloquent($query);

        $dt->addColumn('employee', fn($r) =>
            view('components.employee', ['user' => $r->employee])->render()
        );

        $dt->editColumn('start_date', fn($r) =>
            $r->start_date ? Carbon::parse($r->start_date)->format('Y-m-d') : null
        );
        $dt->editColumn('end_date', fn($r) =>
            $r->end_date ? Carbon::parse($r->end_date)->format('Y-m-d') : null
        );

        $dt->addColumn('action', fn($r) =>
            view('trips._actions', ['row' => $r])->render()
        );

        return $dt->rawColumns(['employee','action']);
    }

    public function query(Trip $model)
    {
        $q = $model->newQuery()
            ->with(['employee:id,name,image,email']);

        if (request('employee') && request('employee') !== 'all') {
            $q->where('employee_id', request('employee'));
        }

if ($s = request('searchText')) {
    $q->where(function ($qq) use ($s) {
        $qq->where('place_of_visit', 'like', "%$s%")
           ->orWhere('purpose_of_visit', 'like', "%$s%")
           ->orWhereHas('employee', function ($e) use ($s) {
               $e->where('name', 'like', "%$s%")
                 ->orWhere('email', 'like', "%$s%");
           });
    });
}


        return $q->select('*');
    }

public function html()
{
    return $this->setBuilder('trips-table')
        ->columns($this->getColumns())
        ->parameters([
            'initComplete' => 'function(){ $(".select-picker").selectpicker(); }',
        ]);
}


    protected function getColumns()
    {
        $cols = [
            __('app.id')           => ['data' => 'id', 'name' => 'id', 'visible' => false],
            __('app.employee')     => ['data' => 'employee', 'name' => 'employee', 'orderable' => false, 'searchable' => false],
            __('app.place')        => ['data' => 'place_of_visit', 'name' => 'place_of_visit'],
            __('app.purpose')      => ['data' => 'purpose_of_visit', 'name' => 'purpose_of_visit'],
            __('app.startDate')    => ['data' => 'start_date', 'name' => 'start_date'],
            __('app.endDate')      => ['data' => 'end_date', 'name' => 'end_date'],
            __('app.description')  => ['data' => 'description', 'name' => 'description', 'visible' => false],
        ];

        $action = Column::computed('action', __('app.action'))
            ->exportable(false)->printable(false)->orderable(false)->searchable(false)
            ->addClass('text-right pr-20');

        return array_merge($cols, [$action]);
    }
}

<?php

namespace App\DataTables;

use App\Models\Complaint;
use Carbon\Carbon;
use Yajra\DataTables\Html\Column;

class ComplaintsDataTable extends BaseDataTable
{
    public function __construct() { parent::__construct(); }

    public function dataTable($query)
    {
        $dt = datatables()->eloquent($query);

        $dt->addColumn('from_user', fn($r) =>
            view('components.employee', ['user' => $r->from])->render()
        );
        $dt->addColumn('against_user', fn($r) =>
            $r->against ? view('components.employee', ['user' => $r->against])->render() : '--'
        );

        $dt->editColumn('complaint_date', fn($r) =>
            $r->complaint_date ? Carbon::parse($r->complaint_date)->format('Y-m-d') : null
        );

        $dt->addColumn('action', fn($r) =>
            view('complaints._actions', ['row' => $r])->render()
        );

        return $dt->rawColumns(['from_user','against_user','action']);
    }

    public function query(Complaint $model)
    {
        $q = $model->newQuery()->with([
            'from:id,name,image,email',
            'against:id,name,image,email',
        ]);

        // Filter by selected employee (match either side)
        if (($emp = request('employee')) && $emp !== 'all') {
            $q->where(function($qq) use ($emp) {
                $qq->where('complaint_from', $emp)
                   ->orWhere('complaint_against', $emp);
            });
        }

        // Text search (title/status or either user's name/email)
        if ($s = request('searchText')) {
            $q->where(function ($qq) use ($s) {
                $qq->where('title', 'like', "%$s%")
                   ->orWhere('status','like', "%$s%")
                   ->orWhereHas('from', function ($u) use ($s) {
                       $u->where('name', 'like', "%$s%")
                         ->orWhere('email','like', "%$s%");
                   })
                   ->orWhereHas('against', function ($u) use ($s) {
                       $u->where('name', 'like', "%$s%")
                         ->orWhere('email','like', "%$s%");
                   });
            });
        }

        return $q->select('*');
    }

    public function html()
    {
        return $this->setBuilder('complaints-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'initComplete' => 'function(){ $(".select-picker").selectpicker(); }',
            ]);
    }

    protected function getColumns()
    {
        $cols = [
            __('app.id')  => ['data'=>'id','name'=>'id','visible'=>false],
            'From'        => ['data'=>'from_user','name'=>'complaint_from','orderable'=>false,'searchable'=>false],
            'Against'     => ['data'=>'against_user','name'=>'complaint_against','orderable'=>false,'searchable'=>false],
            'Title'       => ['data'=>'title','name'=>'title'],
            'Date'        => ['data'=>'complaint_date','name'=>'complaint_date'],
        ];

        $action = Column::computed('action', __('app.action'))
            ->exportable(false)->printable(false)->orderable(false)->searchable(false)
            ->addClass('text-right pr-20');

        return array_merge($cols, [$action]);
    }
}

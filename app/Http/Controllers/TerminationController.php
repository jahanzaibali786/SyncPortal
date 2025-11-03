<?php

// app/Http/Controllers/TerminationController.php
namespace App\Http\Controllers;

use App\DataTables\EmployeeExitsDataTable;
use App\Helper\Reply;
use App\Models\EmployeeExit;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TerminationController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.employees';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {
        if (!request()->ajax()) {
            $this->employees = User::allEmployees();
            $this->designations = \App\Models\Designation::allDesignations();
            $this->departments = \App\Models\Team::all();
            $this->roles = \App\Models\Role::where('name','<>','client')->orderBy('id')->get();
            $this->kind = EmployeeExit::KIND_TERMINATION;
            $this->terminationTypes = EmployeeExit::TERMINATION_TYPES;
        }

        $dt = new EmployeeExitsDataTable(EmployeeExit::KIND_TERMINATION);
        return $dt->render('employees.indexTermination', $this->data);
    }

    public function create()
    {
        $this->kind = EmployeeExit::KIND_TERMINATION;
        $this->employees = User::allEmployees();
        $this->terminationTypes = EmployeeExit::TERMINATION_TYPES;

        $html = view('employees.exits._form', $this->data)->render();
        return Reply::dataOnly(['status'=>'success','html'=>$html,'title'=>__('Add Termination')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'     => 'required|exists:users,id',
            'notice_date'     => 'nullable|string',
            'effective_date'  => 'required|string',
            'termination_type'=> 'required|integer|in:1,2,3',
            'description'     => 'nullable|string',
        ]);

        // Normalize dates from company format to Y-m-d
        $data['notice_date'] = $this->parseAnyDateToYmd($request->notice_date);
        $data['effective_date'] = $this->parseAnyDateToYmd($request->effective_date);

        $data['kind'] = EmployeeExit::KIND_TERMINATION;
        $data['created_by'] = user()->id;

        EmployeeExit::create($data);

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('terminations.index')
        ]);
    }

    public function edit(EmployeeExit $termination)
    {
        abort_unless($termination->kind === EmployeeExit::KIND_TERMINATION, 404);

        $this->exit = $termination;
        $this->kind = EmployeeExit::KIND_TERMINATION;
        $this->employees = User::allEmployees();
        $this->terminationTypes = EmployeeExit::TERMINATION_TYPES;

        $html = view('employees.exits._form', $this->data)->render();
        return Reply::dataOnly(['status'=>'success','html'=>$html,'title'=>__('app.edit')]);
    }

    public function update(Request $request, EmployeeExit $termination)
    {
        abort_unless($termination->kind === EmployeeExit::KIND_TERMINATION, 404);

        $data = $request->validate([
            'employee_id'     => 'required|exists:users,id',
            'notice_date'     => 'nullable|string',
            'effective_date'  => 'required|string',
            'termination_type'=> 'required|integer|in:1,2,3',
            'description'     => 'nullable|string',
        ]);

        $data['notice_date'] = $this->parseAnyDateToYmd($request->notice_date);
        $data['effective_date'] = $this->parseAnyDateToYmd($request->effective_date);

        $termination->update($data);

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route('terminations.index')
        ]);
    }

    public function destroy(EmployeeExit $termination)
    {
        abort_unless($termination->kind === EmployeeExit::KIND_TERMINATION, 404);

        $termination->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), [
            'redirectUrl' => route('terminations.index')
        ]);
    }

    // local helper
    private function parseAnyDateToYmd(?string $value): ?string
    {
        if (!$value) return null;
        try { return Carbon::parse($value)->toDateString(); } catch (\Throwable $e) {}
        try { return Carbon::createFromFormat(company()->date_format, $value)->toDateString(); } catch (\Throwable $e) {}
        return null;
    }
}

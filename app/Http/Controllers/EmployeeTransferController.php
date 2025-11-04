<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeTransfersDataTable;
use App\Helper\Reply;
use App\Models\EmployeeDetails;
use App\Models\EmployeeTransfer;
use App\Models\Designation;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserAuth;
use Carbon\Carbon;

class EmployeeTransferController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();

        // Page title used by layouts.app
        $this->pageTitle = __('Employee Transfers');

        // Require “employees” module enabled (same pattern as EmployeeController)
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));
            return $next($request);
        });
    }


    private function buildSidebarPerms(User|UserAuth|null $user): array
    {
        // Normalize to User
        if ($user instanceof UserAuth) {
            $user = User::where('user_auth_id', $user->id)->first();
        }

        $p = function (string $key) use ($user) {
            if (!$user || !method_exists($user, 'permission')) {
                return 5; // deny by default
            }
            $val = $user->permission($key);
            return $val === null ? 5 : $val;
        };

        return [
            // dashboards
            'view_overview_dashboard'   => $p('view_overview_dashboard'),
            'view_project_dashboard'    => $p('view_project_dashboard'),
            'view_client_dashboard'     => $p('view_client_dashboard'),
            'view_hr_dashboard'         => $p('view_hr_dashboard'),
            'view_ticket_dashboard'     => $p('view_ticket_dashboard'),
            'view_finance_dashboard'    => $p('view_finance_dashboard'),

            // modules & items your Blade reads
            'view_clients'              => $p('view_clients'),
            'view_employees'            => $p('view_employees'),
            'view_leave'                => $p('view_leave'),
            'view_attendance'           => $p('view_attendance'),
            'view_holiday'              => $p('view_holiday'),
            'view_shift_roster'         => $p('view_shift_roster'),
            'view_contract'             => $p('view_contract'),
            'view_projects'             => $p('view_projects'),
            'view_tasks'                => $p('view_tasks'),
            'view_timelogs'             => $p('view_timelogs'),
            'view_estimates'            => $p('view_estimates'),
            'view_invoices'             => $p('view_invoices'),
            'view_payments'             => $p('view_payments'),
            'view_expenses'             => $p('view_expenses'),
            'view_bankaccount'          => $p('view_bankaccount'),
            'view_lead_proposals'       => $p('view_lead_proposals'),
            'view_tickets'              => $p('view_tickets'),
            'view_events'               => $p('view_events'),
            'view_product'              => $p('view_product'),
            'view_order'                => $p('view_order'),
            'view_notice'               => $p('view_notice'),
            'view_knowledgebase'        => $p('view_knowledgebase'),
            'view_client_note'          => $p('view_client_note'),
            'view_lead'                 => $p('view_lead'),
            'view_deals'                => $p('view_deals'),
            'add_lead'                  => $p('add_lead'),
            'view_designation'          => $p('view_designation'),
            'view_department'           => $p('view_department'),
            'view_appreciation'         => $p('view_appreciation'),
            'manage_award'              => $p('manage_award'),
            'view_task_report'          => $p('view_task_report'),
            'view_time_log_report'      => $p('view_time_log_report'),
            'view_finance_report'       => $p('view_finance_report'),
            'view_income_expense_report' => $p('view_income_expense_report'),
            'view_leave_report'         => $p('view_leave_report'),
            'view_attendance_report'    => $p('view_attendance_report'),
            'view_lead_report'          => $p('view_lead_report'),
            'view_sales_report'         => $p('view_sales_report'),
            'manage_company_setting'    => $p('manage_company_setting'),
            'add_employees'             => $p('add_employees'),
        ];
    }


    /** ===========================
     *  Shared layout variables
     *  =========================== */
    private function layoutShared(): array
    {
        $user = auth()->user();

        // Topbar counts
        $unread = $user && method_exists($user, 'unreadNotifications')
            ? $user->unreadNotifications()->count()
            : 0;
        $activeTimers = 0;

        // Company switcher (safe defaults)
        $userCompanies = collect();
        if ($user) {
            if (method_exists($user, 'companyUsers')) {
                $userCompanies = $user->companyUsers()->with('company')->get();
            } elseif (method_exists($user, 'companies')) {
                $userCompanies = $user->companies()->with('company')->get();
            }
        }

        // Theme
        $appTheme = \App\Models\ThemeSetting::where('panel', 'admin')->first();
        if (!$appTheme) {
            $appTheme = (object)['sidebar_theme' => 'dark'];
        }

        // Names
        $appName = config('app.name', 'Application');
        $companyName = '';
        if (function_exists('company') && company()) {
            $companyName = company()->company_name;
        } elseif ($user && method_exists($user, 'company') && $user->company) {
            $companyName = $user->company->company_name;
        }

        // Permissions map expected by sections/menu.blade.php
        $perm = function (string $key) use ($user) {
            return $user && method_exists($user, 'permission')
                ? ($user->permission($key) ?? 5)
                : 5;
        };

        $sidebarUserPermissions = $this->buildSidebarPerms($user);

        return [
            'pageTitle'               => $this->pageTitle,
            'unreadNotificationCount' => $unread,
            'activeTimerCount'        => $activeTimers,
            'userCompanies'           => $userCompanies,
            'appTheme'                => $appTheme,
            'appName'                 => $appName,
            'companyName'             => $companyName,
            'sidebarUserPermissions'  => $sidebarUserPermissions,
            'user'                    => $user,
        ];
    }


    /** ===========================
     *  Index (DataTable)
     *  =========================== */
    public function index(EmployeeTransfersDataTable $dataTable)
    {
        // match Employees page: preload filter data ONLY when not ajax
        if (!request()->ajax()) {
            $data = [
                'employees'    => User::allEmployees(),                 // for employee filter
                'departments'  => Team::all(),                           // for department filter
                'designations' => Designation::allDesignations(),        // to match your view
                'roles'        => Role::where('name', '<>', 'client')->orderBy('id')->get(),
            ];
        } else {
            $data = [];
        }

        // merge layout vars + page vars the way EmployeeController does ($this->data)
        $payload = array_merge($this->data, $this->layoutShared(), $data);

        return $dataTable->render('employees.transfers.index', $payload);
    }

    /** ===========================
     *  Create
     *  =========================== */
    public function create()
    {
        $payload = array_merge($this->data, $this->layoutShared(), [
            'employees'   => User::allEmployees(),
            'departments' => Team::all(),
            'transfer'    => null,
        ]);

        if (request()->ajax()) {
            $html = view('employees.transfers.create', $payload)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('Add Transfer')]);
        }

        return view('employees.transfers.create', $payload);
    }

    /** ===========================
     *  Store (no form-request)
     *  =========================== */
    private function toYmd(string $companyDate): string
    {
        $fmt = company()->date_format; // e.g. d-m-Y
        return Carbon::createFromFormat($fmt, $companyDate)->format('Y-m-d');
    }

private function normalizeCompanyDate(?string $val): ?string
{
    if (!$val) return null;

    $fmt = function_exists('company') && company() ? company()->date_format : 'Y-m-d';

    // Try exact company format first
    try {
        return Carbon::createFromFormat($fmt, $val)->format('Y-m-d');
    } catch (\Throwable $e) {}

    // Fallback to Carbon::parse for things like 2025-11-01
    try {
        return Carbon::parse($val)->format('Y-m-d');
    } catch (\Throwable $e) {}

    return null;
}


public function store(Request $request)
{
    $request->merge([
        'transfer_date' => $this->normalizeCompanyDate($request->input('transfer_date'))
    ]);

    $request->validate([
        'employee_id'        => 'required|integer|exists:users,id',
        'transfer_date'      => 'required|date', // now normalized
        'from_department_id' => 'nullable|integer|exists:teams,id',
        'to_department_id'   => 'nullable|integer|exists:teams,id',
        'description'        => 'nullable|string|max:191',
    ]);

    $t = new EmployeeTransfer();
    $t->employee_id        = $request->employee_id;
    $t->from_department_id = $request->from_department_id;
    $t->to_department_id   = $request->to_department_id;
    $t->transfer_date      = $request->transfer_date; // already Y-m-d
    $t->description        = $request->description;
    $t->created_by         = auth()->id();
    $t->save();

    if ($request->boolean('apply_to_profile') && $t->to_department_id) {
        EmployeeDetails::where('user_id', $t->employee_id)
            ->update(['department_id' => $t->to_department_id]);
    }

    return Reply::successWithData(__('messages.recordSaved'), [
        'redirectUrl' => route('transfers.index')
    ]);
}


public function update(Request $request, EmployeeTransfer $transfer)
{
    $request->merge([
        'transfer_date' => $this->normalizeCompanyDate($request->input('transfer_date'))
    ]);

    $request->validate([
        'employee_id'        => 'required|integer|exists:users,id',
        'transfer_date'      => 'required|date',
        'from_department_id' => 'nullable|integer|exists:teams,id',
        'to_department_id'   => 'nullable|integer|exists:teams,id',
        'description'        => 'nullable|string|max:191',
    ]);

    $transfer->employee_id        = $request->employee_id;
    $transfer->from_department_id = $request->from_department_id;
    $transfer->to_department_id   = $request->to_department_id;
    $transfer->transfer_date      = $request->transfer_date; // Y-m-d
    $transfer->description        = $request->description;
    $transfer->save();

    if ($request->boolean('apply_to_profile') && $transfer->to_department_id) {
        EmployeeDetails::where('user_id', $transfer->employee_id)
            ->update(['department_id' => $transfer->to_department_id]);
    }

    return Reply::successWithData(__('messages.updateSuccess'), [
        'redirectUrl' => route('transfers.index')
    ]);
}



    /** ===========================
     *  Edit
     *  =========================== */
    public function edit(EmployeeTransfer $transfer)
    {
        $payload = array_merge($this->data, $this->layoutShared(), [
            'employees'   => User::allEmployees(),
            'departments' => Team::all(),
            'transfer'    => $transfer,
        ]);

        if (request()->ajax()) {
            $html = view('employees.transfers.edit', $payload)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('Edit Transfer')]);
        }

        return view('employees.transfers.edit', $payload);
    }

    /** ===========================
     *  Update (no form-request)
     *  =========================== */
    // public function update(Request $request, EmployeeTransfer $transfer)
    // {
    //     $request->validate([
    //         'employee_id'        => 'required|integer|exists:users,id',
    //         'transfer_date'      => 'required|date',
    //         'from_department_id' => 'nullable|integer|exists:teams,id',
    //         'to_department_id'   => 'nullable|integer|exists:teams,id',
    //         'description'        => 'nullable|string|max:191',
    //     ]);

    //     $transfer->employee_id        = $request->employee_id;
    //     $transfer->from_department_id = $request->from_department_id;
    //     $transfer->to_department_id   = $request->to_department_id;
    //     $transfer->transfer_date      = $request->transfer_date;
    //     $transfer->description        = $request->description;
    //     $transfer->save();

    //     if ($request->boolean('apply_to_profile') && $transfer->to_department_id) {
    //         EmployeeDetails::where('user_id', $transfer->employee_id)->update(['department_id' => $transfer->to_department_id]);
    //     }

    //     return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('transfers.index')]);
    // }

    /** ===========================
     *  Destroy
     *  =========================== */
    public function destroy(EmployeeTransfer $transfer)
    {
        $transfer->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}

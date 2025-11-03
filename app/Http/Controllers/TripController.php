<?php

namespace App\Http\Controllers;

use App\DataTables\TripsDataTable;
use App\Helper\Reply;
use App\Models\Trip;
use App\Models\User;
use App\Models\UserAuth; // <-- add this
use Illuminate\Http\Request;
use Carbon\Carbon;

class TripController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('Trips & Travels');

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

    private function normalizeCompanyDate(?string $val): ?string
    {
        if (!$val) return null;
        $fmt = function_exists('company') && company() ? company()->date_format : 'Y-m-d';
        try { return Carbon::createFromFormat($fmt, $val)->format('Y-m-d'); } catch (\Throwable $e) {}
        try { return Carbon::parse($val)->format('Y-m-d'); } catch (\Throwable $e) {}
        return null;
    }

    public function index(TripsDataTable $dataTable)
    {
        $payload = array_merge($this->data, $this->layoutShared(), [
            'employees' => User::allEmployees(),
        ]);

        return $dataTable->render('trips.index', $payload);
    }

    public function create()
    {
        $payload = array_merge($this->data, $this->layoutShared(), [
            'employees' => User::allEmployees(),
            'trip'      => null,
        ]);

        if (request()->ajax()) {
            $html = view('trips.create', $payload)->render();
            return Reply::dataOnly(['status'=>'success','html'=>$html,'title'=>__('Add Trip')]);
        }

        return view('trips.create', $payload);
    }

    public function store(Request $request)
    {
        $request->merge([
            'start_date' => $this->normalizeCompanyDate($request->input('start_date')),
            'end_date'   => $this->normalizeCompanyDate($request->input('end_date')),
        ]);

        $request->validate([
            'employee_id'      => 'required|integer|exists:users,id',
            'place_of_visit'   => 'required|string|max:191',
            'purpose_of_visit' => 'required|string|max:191',
            'start_date'       => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'description'      => 'nullable|string|max:191',
        ]);

        Trip::create([
            'employee_id'      => $request->employee_id,
            'place_of_visit'   => $request->place_of_visit,
            'purpose_of_visit' => $request->purpose_of_visit,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'description'      => $request->description,
            'created_by'       => auth()->id(),
        ]);

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('trips.index')
        ]);
    }

    public function edit(Trip $trip)
    {
        $payload = array_merge($this->data, $this->layoutShared(), [
            'employees' => User::allEmployees(),
            'trip'      => $trip,
        ]);

        if (request()->ajax()) {
            $html = view('trips.edit', $payload)->render();
            return Reply::dataOnly(['status'=>'success','html'=>$html,'title'=>__('Edit Trip')]);
        }

        return view('trips.edit', $payload);
    }

    public function update(Request $request, Trip $trip)
    {
        $request->merge([
            'start_date' => $this->normalizeCompanyDate($request->input('start_date')),
            'end_date'   => $this->normalizeCompanyDate($request->input('end_date')),
        ]);

        $request->validate([
            'employee_id'      => 'required|integer|exists:users,id',
            'place_of_visit'   => 'required|string|max:191',
            'purpose_of_visit' => 'required|string|max:191',
            'start_date'       => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'description'      => 'nullable|string|max:191',
        ]);

        $trip->fill([
            'employee_id'      => $request->employee_id,
            'place_of_visit'   => $request->place_of_visit,
            'purpose_of_visit' => $request->purpose_of_visit,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'description'      => $request->description,
        ])->save();

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route('trips.index')
        ]);
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}

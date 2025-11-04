<?php

namespace App\Http\Controllers;

use App\DataTables\ComplaintsDataTable;
use App\Helper\Reply;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ComplaintController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('Complaints');

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));
            return $next($request);
        });
    }

    private function layoutShared(): array
    {
        $user = auth()->user();

        $unread = $user && method_exists($user, 'unreadNotifications')
            ? $user->unreadNotifications()->count()
            : 0;

        $appTheme = \App\Models\ThemeSetting::where('panel', 'admin')->first() ?? (object)['sidebar_theme' => 'dark'];
        $appName = config('app.name', 'Application');
        $companyName = '';
        if (function_exists('company') && company()) {
            $companyName = company()->company_name;
        } elseif ($user && method_exists($user, 'company') && $user->company) {
            $companyName = $user->company->company_name;
        }

        return [
            'pageTitle'               => $this->pageTitle,
            'unreadNotificationCount' => $unread,
            'appTheme'                => $appTheme,
            'appName'                 => $appName,
            'companyName'             => $companyName,
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

    public function index(ComplaintsDataTable $dataTable)
    {
        if (!request()->ajax()) {
            $data = [
                'users' => \App\Models\User::select('id','name')->orderBy('name')->get(),
            ];
        } else {
            $data = [];
        }
    
        $payload = array_merge($this->data, $this->layoutShared(), $data);
        return $dataTable->render('complaints.index', $payload);
    }
    

    public function create()
    {
        $payload = array_merge($this->data, $this->layoutShared(), [
            'complaint' => null,
            'users'     => User::select('id','name')->orderBy('name')->get(),
        ]);

        if (request()->ajax()) {
            $html = view('complaints.create', $payload)->render();
            return Reply::dataOnly(['status'=>'success','html'=>$html,'title'=>__('Add Complaint')]);
        }

        return view('complaints.create', $payload);
    }

    public function store(Request $request)
    {
        $request->merge([
            'complaint_date' => $this->normalizeCompanyDate($request->input('complaint_date')),
        ]);

        $request->validate([
            'complaint_from'   => 'required|integer|exists:users,id',
            'complaint_against'=> 'nullable|integer|exists:users,id|different:complaint_from',
            'title'            => 'required|string|max:191',
            'complaint_date'   => 'required|date',
            'status'           => 'nullable|in:pending,in_progress,resolved',
            'description'      => 'nullable|string',
        ]);

        $c = new Complaint();
        $c->fill($request->only([
            'complaint_from','complaint_against','title','complaint_date','status','description'
        ]));
        $c->created_by = auth()->id();
        $c->save();

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('complaints.index')
        ]);
    }

    public function edit(Complaint $complaint)
    {
        $payload = array_merge($this->data, $this->layoutShared(), [
            'complaint' => $complaint,
            'users'     => User::select('id','name')->orderBy('name')->get(),
        ]);

        if (request()->ajax()) {
            $html = view('complaints.edit', $payload)->render();
            return Reply::dataOnly(['status'=>'success','html'=>$html,'title'=>__('Edit Complaint')]);
        }

        return view('complaints.edit', $payload);
    }

    public function update(Request $request, Complaint $complaint)
    {
        $request->merge([
            'complaint_date' => $this->normalizeCompanyDate($request->input('complaint_date')),
        ]);

        $request->validate([
            'complaint_from'   => 'required|integer|exists:users,id',
            'complaint_against'=> 'nullable|integer|exists:users,id|different:complaint_from',
            'title'            => 'required|string|max:191',
            'complaint_date'   => 'required|date',
            'status'           => 'nullable|in:pending,in_progress,resolved',
            'description'      => 'nullable|string',
        ]);

        $complaint->fill($request->only([
            'complaint_from','complaint_against','title','complaint_date','status','description'
        ]));
        $complaint->save();

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route('complaints.index')
        ]);
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}

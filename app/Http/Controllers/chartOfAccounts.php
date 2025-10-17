<?php

namespace App\Http\Controllers;

use App\DataTables\ChartOfAccountsDataTable;
use App\Helper\Reply;
use App\Http\Requests\StoreChartOfAccount;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class chartOfAccounts extends AccountBaseController
{
     public function __construct()
    {
        parent::__construct();
        $this->pageTitle =  __('app.menu.chartOfAccounts');
        // $this->middleware(function ($request, $next) {
        //     abort_403(!in_array('vouchers', $this->user->modules));
        //     return $next($request);
        // });
    }

    public function index(ChartOfAccountsDataTable $dataTable, Request $request)
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }
        return $dataTable->render('accounting.chartOfAccounts.index', $this->data);
    }
    public function getParents(Request $request)
    {
        $subTypeId = $request->sub_type_id;

        $parents = \App\Models\ChartOfAccount::where('chart_of_account_sub_type_id', $subTypeId)->get(['id', 'name']);

        return response()->json($parents);
    }
    // create
    public function create()
    {
        // Find the chart of account
        $this->accountTypes =  ChartOfAccountType::with('subtypes')->where('company_id', company()->id)->get()
        ->groupBy(function ($type) {
            return $type->name; // parent group name e.g. Assets, Liabilities, etc.
        });
        $this->chartOfAccounts = ChartOfAccount::where('company_id', company()->id)->get();

            
         $this->view = 'accounting.chartOfAccounts.ajax.create'; // this is the view that will be loaded in the modal

            if (request()->ajax()) {
                return $this->returnAjax($this->view);
            }
        return view('accounting.chartOfAccounts.create', $this->data);
    }

    public function store(StoreChartOfAccount $request)
    {
        $chartofaccountsubtype = ChartOfAccountSubType::where('id', $request->account_sub_type_id)->first();
        // Create the chart of account
        $coa = new ChartOfAccount();
        $coa->name = $request->name;
        $coa->code = $request->code;
        $coa->chart_of_account_type_id = $chartofaccountsubtype->chart_of_account_type_id;
        $coa->chart_of_account_sub_type_id = $request->account_sub_type_id;
        $coa->parent_id = $request->parent_id;
        $coa->description = $request->description;
        $coa->company_id = company()->id;
        $coa->save();

        // Fetch updated list if needed
        $allAccounts = ChartOfAccount::all();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('coa.index');
        }

        return Reply::successWithData(
            __('messages.recordSaved'),
            [
                'chartOfAccounts' => $allAccounts,
                'redirectUrl' => $redirectUrl
            ]
        );
    }

    public function update(Request $request, $id)
    {

        try {
            // Find the chart of account
            $chartOfAccount = ChartOfAccount::where('company_id', company()->id)
                ->findOrFail($id);
            
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('chart_of_accounts', 'name')
                        ->where('company_id', company()->id)
                        ->ignore($chartOfAccount->id)
                ],
                'code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('chart_of_accounts', 'code')
                        ->where('company_id', company()->id)
                        ->ignore($chartOfAccount->id)
                ]
            ], [
                'name.required' => 'Account name is required.',
                'name.unique' => 'This account name already exists.',
                'name.max' => 'Account name cannot exceed 255 characters.',
                'code.required' => 'Account code is required.',
                'code.unique' => 'This account code already exists.',
                'code.max' => 'Account code cannot exceed 50 characters.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update the chart of account
            $chartOfAccount->update([
                'name' => $request->name,
                'code' => $request->code,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chart of Account updated successfully.',
                'data' => $chartOfAccount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the account.'
            ], 500);
        }
    }
}

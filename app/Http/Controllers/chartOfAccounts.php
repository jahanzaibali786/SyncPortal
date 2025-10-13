<?php

namespace App\Http\Controllers;

use App\DataTables\ChartOfAccountsDataTable;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class chartOfAccounts extends AccountBaseController
{
     public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.chartOfAccounts';
        // $this->middleware(function ($request, $next) {
        //     abort_403(!in_array('vouchers', $this->user->modules));
        //     return $next($request);
        // });
    }

    public function index(ChartOfAccountsDataTable $dataTable, Request $request)
    {
         $this->pageTitle = 'Chart of Accounts';

        if ($request->ajax()) {
            return $dataTable->ajax();
        }
        // dd($this->data);
        return $dataTable->render('accounting.chartOfAccounts.index', $this->data);
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

<?php

namespace App\Http\Controllers;

use App\DataTables\TrialBalanceDataTable;
use App\Models\ChartOfAccount;
use Carbon\Carbon;

class TrialBalanceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Trial Balance';
        // $this->middleware(function ($request, $next) {
        //     abort_403(!in_array('accounting', $this->user->modules));
        //     return $next($request);
        // });
    }

    public function index(TrialBalanceDataTable $dataTable)
    {
        // $viewPermission = user()->permission('view_accounting');
        // abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->accounts = ChartOfAccount::where('company_id', company()->id)
            ->orderBy('chart_of_account_type_id')
            ->orderBy('chart_of_account_sub_type_id')
            ->get();

        return $dataTable->render('accounting.trial-balance.index', $this->data);
    }
}
<script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>

<div class="row">
    <div class="col-md-12">
        <x-alert type="secondary" icon="info-circle">
            @lang('messages.earningChartNote') ({{ company()->currency->currency_code }})
        </x-alert>
    </div>
</div>

<div class="row">
    @if (in_array('invoices', user_modules()) &&
            (in_array('total_paid_invoices', $activeWidgets) || in_array('total_unpaid_invoices', $activeWidgets)))

        {{-- Total Paid Invoices --}}
        @if (in_array('total_paid_invoices', $activeWidgets))
            <div class="col-xl-4 col-lg-6 col-md-6">
                <a href="javascript:;" id="totalPaidInvoices">
                    <x-cards.widget :title="__('modules.dashboard.totalPaidInvoices')" :value="$totalPaidInvoice" icon="file-invoice text-blue" />
                </a>
            </div>
        @endif

        {{-- Total Unpaid Invoices --}}
        @if (in_array('total_unpaid_invoices', $activeWidgets))
            <div class="col-xl-4 col-lg-6 col-md-6">
                <a href="javascript:;" id="totalPendingInvoices">
                    <x-cards.widget :title="__('modules.dashboard.totalUnpaidInvoices')" :value="$totalUnPaidInvoice" icon="file-invoice text-red" />
                </a>
            </div>
        @endif

    @endif


    @if (in_array('invoices', user_modules()) &&
            (in_array('total_expenses', $activeWidgets) || in_array('total_earnings', $activeWidgets)))

        {{-- Total Expenses --}}
        @if (in_array('total_expenses', $activeWidgets))
            <div class="col-xl-4 col-lg-6 col-md-6">
                <a href="javascript:;" id="totalExpenses">
                    <x-cards.widget :title="__('modules.dashboard.totalExpenses')" :value="currency_format($totalExpenses, company()->currency_id)" icon="coins text-blue" />
                </a>
            </div>
        @endif

        {{-- Total Earnings --}}
        @if (in_array('total_earnings', $activeWidgets))
            <div class="col-xl-4 col-lg-6 col-md-6">
                <a href="javascript:;" id="totalEarnings">
                    <x-cards.widget :title="__('modules.dashboard.totalEarnings')" :value="currency_format($totalEarnings, company()->currency_id)" icon="coins text-dark-green" />
                </a>
            </div>
        @endif

    @endif


    @if (in_array('invoices', user_modules()) && in_array('total_pending_amount', $activeWidgets))
        <div class="col-xl-4 col-lg-6 col-md-6">
            <a href="javascript:;" id="totalPendingAmount">
                <x-cards.widget :title="__('modules.dashboard.totalPendingAmount')" :value="currency_format($totalPendingAmount, company()->currency_id)" icon="coins" />
            </a>
        </div>
    @endif

</div>

<div class="row">
    @if (in_array('invoices', user_modules()) && in_array('invoice_overview', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-4">
            <x-cards.data :title="__('modules.dashboard.invoiceOverview') .
                ' <i class=\'fa fa-question-circle\' data-toggle=\'popover\' data-placement=\'top\' data-content=\'' .
                __('messages.invoicesWidgetMessage') .
                '\' data-trigger=\'hover\'></i>'">
                @if (array_sum($invoiceOverviewChartData['values']) > 0)
                    <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen"
                        data-chart-id="task-chart1" data-chart-data="{{ json_encode($invoiceOverviewChartData) }}"><i
                            class="fas fa-expand float-right mr-3"></i></a>
                @endif
                <x-pie-chart id="task-chart1" :labels="$invoiceOverviewChartData['labels']" :values="$invoiceOverviewChartData['values']" :colors="$invoiceOverviewChartData['colors']" height="300"
                    width="300" />
            </x-cards.data>
        </div>
    @endif

    @if (in_array('estimates', user_modules()) && in_array('estimate_overview', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-4">
            <x-cards.data :title="__('modules.dashboard.estimateOverview') .
                ' <i class=\'fa fa-question-circle\' data-toggle=\'popover\' data-placement=\'top\' data-content=\'' .
                __('messages.estimatesWidgetMessage') .
                '\' data-trigger=\'hover\'></i>'">
                @if (array_sum($estimateOverviewChartData['values']) > 0)
                    <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen"
                        data-chart-id="task-chart2" data-chart-data="{{ json_encode($estimateOverviewChartData) }}"><i
                            class="fas fa-expand float-right mr-3"></i></a>
                @endif
                <x-pie-chart id="task-chart2" :labels="$estimateOverviewChartData['labels']" :values="$estimateOverviewChartData['values']" :colors="$estimateOverviewChartData['colors']" height="300"
                    width="300" />
            </x-cards.data>
        </div>
    @endif

    @if (in_array('leads', user_modules()) && in_array('proposal_overview', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-4">
            <x-cards.data :title="__('modules.dashboard.proposalOverview') .
                ' <i class=\'fa fa-question-circle\' data-toggle=\'popover\' data-placement=\'top\' data-content=\'' .
                __('messages.proposalsWidgetMessage') .
                '\' data-trigger=\'hover\'></i>'">
                @if (array_sum($proposalOverviewChartData['values']) > 0)
                    <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen"
                        data-chart-id="task-chart3" data-chart-data="{{ json_encode($proposalOverviewChartData) }}"><i
                            class="fas fa-expand float-right mr-3"></i></a>
                @endif
                <x-pie-chart id="task-chart3" :labels="$proposalOverviewChartData['labels']" :values="$proposalOverviewChartData['values']" :colors="$proposalOverviewChartData['colors']" height="300"
                    width="300" />
            </x-cards.data>
        </div>
    @endif

    @if (in_array('payments', user_modules()) && in_array('earnings_by_client', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-4">
            <x-cards.data :title="__('modules.dashboard.clientWiseEarnings') .
                ' <i class=\'fa fa-question-circle\' data-toggle=\'popover\' data-placement=\'top\' data-content=\'' .
                __('messages.earningChartNote') .
                '\' data-trigger=\'hover\'></i>'">
                <x-bar-chart id="task-chart4" :chartData="$clientEarningChart" height="300" />
            </x-cards.data>
        </div>
    @endif

    @if (in_array('payments', user_modules()) &&
            in_array('projects', user_modules()) &&
            in_array('earnings_by_projects', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-4">
            <x-cards.data :title="__('modules.dashboard.earningsByProjects') .
                ' <i class=\'fa fa-question-circle\' data-toggle=\'popover\' data-placement=\'top\' data-content=\'' .
                __('messages.earningChartNote') .
                '\' data-trigger=\'hover\'></i>'">
                <x-bar-chart id="task-chart5" :chartData="$projectEarningChartData" height="300" />
            </x-cards.data>
        </div>
    @endif

</div>

<script>
    $('#save-dashboard-widget').click(function() {
        $.easyAjax({
            url: "{{ route('dashboard.widget', 'admin-finance-dashboard') }}",
            container: '#dashboardWidgetForm',
            blockUI: true,
            type: "POST",
            redirect: true,
            data: $('#dashboardWidgetForm').serialize(),
            success: function() {
                window.location.reload();
            }
        })
    });

    $('#totalPendingAmount').click(function() {
        var dateRange = $('#datatableRange2').data('daterangepicker');
        var startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
        var endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');

        startDate = encodeURIComponent(startDate);
        endDate = encodeURIComponent(endDate);

        var url = "{{ route('invoices.index') }}";
        string = `?status=pending&start=${startDate}&end=${endDate}`;
        url += string;
        window.location.href = url;
    });

    $('#totalEarnings').click(function() {
        var dateRange = getDateRange();
        var url = `{{ route('income-expense-report.index') }}`;

        string = `?start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('#totalPaidInvoices').click(function() {
        var dateRange = getDateRange();
        var url = `{{ route('invoices.index') . '?status=paid' }}`;

        string = `&start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('#totalPendingInvoices').click(function() {
        var dateRange = getDateRange();
        var url = `{{ route('invoices.index') . '?status=pending' }}`;

        string = `&start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    function getDateRange() {
        var dateRange = $('#datatableRange2').data('daterangepicker');
        var startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
        var endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');

        startDate = encodeURIComponent(startDate);
        endDate = encodeURIComponent(endDate);

        var data = [];
        data['startDate'] = startDate;
        data['endDate'] = endDate;

        return data;
    }
</script>

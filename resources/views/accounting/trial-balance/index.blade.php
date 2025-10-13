@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('filter-section')
    <style>
        .content-wrapper {
            background: #fff !important;
            padding: 0px 100px 30px !important;
        }

        .dt-buttons {
            position: fixed !important;
            top: 11% !important;
            right: 10px !important;
            z-index: 1 !important;
        }

        .table-scroll {
            max-height: 600px;
            overflow: auto;
            position: relative;
        }

        .trial-balance-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .trial-balance-table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
            padding: 12px 8px;
        }

        /* Row Styling */
        .trial-balance-table tbody tr {
            background-clip: padding-box;
            transition: all 0.2s ease;
        }

        .trial-balance-table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        /* Header rows */
        .account-header {
            background-color: #e9ecef !important;
            font-weight: bold;
            border-top: 2px solid #adb5bd;
            border-bottom: 1px solid #dee2e6;
        }

        .account-header td {
            padding: 12px 8px !important;
            background-color: #e9ecef !important;
        }

        .account-header-text {
            color: #495057;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        /* Detail account rows */
        .account-detail {
            background-color: #ffffff;
        }

        .account-detail.hidden-row {
            display: none !important;
        }

        .account-detail td {
            padding: 8px 8px 8px 30px !important;
            border-bottom: 1px solid #f1f1f1;
        }

        /* Subtotal rows */
        .account-subtotal {
            background-color: #f8f9fa !important;
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }

        .account-subtotal.hidden-row {
            display: none !important;
        }

        .account-subtotal td {
            padding: 10px 8px 10px 30px !important;
            font-weight: 600;
        }

        .account-subtotal-text {
            color: #007bff;
            font-size: 13px;
        }

        /* Grand total row */
        .grand-total {
            background-color: #f1f3f4 !important;
            border-top: 3px solid #6c757d;
            border-bottom: 3px solid #6c757d;
        }

        .grand-total td {
            padding: 15px 8px !important;
            font-weight: bold;
        }

        .grand-total-text {
            color: #dc3545;
            font-size: 16px;
            font-weight: 700;
        }

        /* Net income row */
        .net-income {
            background-color: #f8f9fa !important;
            border-top: 1px solid #dee2e6;
        }

        .net-income td {
            padding: 12px 8px !important;
        }

        .net-income-text {
            color: #17a2b8;
            font-weight: 600;
        }

        /* Toggle button styling */
        .toggle-btn {
            cursor: pointer;
            margin-right: 8px;
            display: inline-block;
            width: 16px;
            text-align: center;
            transition: all 0.2s ease;
        }

        .toggle-btn:hover {
            color: #007bff;
        }

        .toggle-icon {
            font-size: 12px;
            color: #6c757d;
            transition: all 0.2s ease;
        }

        .toggle-btn.expanded .toggle-icon {
            color: #007bff;
        }

        .toggle-btn.collapsed .fa-plus-square:before {
            content: "\f0fe";
            /* fa-plus-square */
        }

        .toggle-btn.expanded .fa-plus-square:before {
            content: "\f146";
            /* fa-minus-square */
        }

        /* Indentation */
        .indent-spacer {
            display: inline-block;
            width: 20px;
        }

        /* Ledger link styling */
        .ledger-link {
            color: #007bff;
            text-decoration: none;
        }

        .ledger-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        /* Text alignment */
        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        /* Amount styling */
        .text-success {
            color: #28a745 !important;
            font-weight: 500;
        }

        .text-danger {
            color: #dc3545 !important;
            font-weight: 500;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 15px !important;
            }

            .account-detail td {
                padding: 6px 4px 6px 20px !important;
                font-size: 13px;
            }

            .toggle-btn {
                margin-right: 4px;
            }
        }

        /* Expand/Collapse animation */
        .trial-balance-table tbody tr {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .trial-balance-table tbody tr.hidden-row {
            opacity: 0;
            transform: translateY(-10px);
        }

        /* Loading state */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.9) !important;
            color: #6c757d !important;
            border: 1px solid #dee2e6 !important;
        }
    </style>

    <x-filters.filter-box>
        {{-- Date filter --}}
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.date')</p>
            <div class="select-status d-flex">
                <input type="text"
                    class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        
        {{-- Type filter --}}
        <div class="select-box d-flex p-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Type</p>
            <div class="select-status d-flex">
                <select id="filter-type" class="form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey">
                    <option value="">All</option>
                    <option value="Asset">Asset</option>
                    <option value="Liability">Liability</option>
                    <option value="Equity">Equity</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
        </div>

        {{-- Subtype filter --}}
        <div class="select-box d-flex border-right-grey border-right-grey-sm-0 p-2">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Subtype</p>
            <div class="select-status d-flex">
                <select id="filter-subtype" class="form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey">
                    <option value="">All</option>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="receivable">Receivable</option>
                    <option value="payable">Payable</option>
                    <option value="inventory">Inventory</option>
                    <option value="fixed_asset">Fixed Asset</option>
                </select>
            </div>
        </div>

        
        <x-slot name="action">
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary class="btn-xs d-none d-lg-block d-md-block mr-2" id="reset-filters"
                    icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
        </x-slot>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            <div
                class="d-lg-flex d-md-flex d-block justify-content-between p-4 bg-white border-bottom-grey text-capitalize">
                <h4 class="heading-h4 mb-0">
                    @lang('modules.accounts.trialBalance')
                    <span class="text-lightest f-12 ml-2" id="date-range-display"></span>
                </h4>
                {{-- <div class="d-flex align-items-center">
                    <small class="text-muted mr-3">
                        <i class="fa fa-info-circle"></i> Click on account type headers to expand/collapse
                    </small>
                </div> --}}
                <div class="select-box d-flex pr-2 border-right-grey justify-content-end border-right-grey-sm-0">
                    <div class="select-status d-flex">
                        <button type="button" title="Expand All" class="btn btn-sm btn-outline-primary mr-2"
                            id="expand-all">
                            <i class="fa fa-expand"></i>
                        </button>
                        <button type="button" title="Collapse All" class="btn btn-sm btn-outline-secondary"
                            id="collapse-all">
                            <i class="fa fa-compress"></i>
                        </button>

                    </div>
                </div>
            </div>

            <div class="table-responsive pt-0 px-20 pb-20 table-scroll">
                {!! $dataTable->table(['class' => 'table table-hover border-0 w-100 trial-balance-table']) !!}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        let trialBalanceTable;
        let expandedGroups = new Set(); // Track which groups are expanded

        // Initialize DataTable
        $(document).ready(function() {
            trialBalanceTable = window.LaravelDataTables["trial-balance-table"];

            // Initially collapse all detail rows
            // initializeCollapsedState();
            initializeExpandedState();
        });

        // Push extra filters to DataTable Ajax
        $('#trial-balance-table').on('preXhr.dt', function(e, settings, data) {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                data.startDate = null;
                data.endDate = null;
            } else {
                data.startDate = dateRangePicker.startDate.format('{!! company()->moment_date_format !!}');
                data.endDate = dateRangePicker.endDate.format('{!! company()->moment_date_format !!}');
            }

            // Send dropdown values
            data.subtype = $('#filter-subtype').val();
            data.type = $('#filter-type').val();
        });

        // Initialize collapsed state after table draw
        $('#trial-balance-table').on('draw.dt', function() {
            initializeExpandedState();
            restoreExpandedState();
        });

        function initializeExpandedState() {
            // Hide all child rows initially
            $('#trial-balance-table tbody tr.child-row').removeClass('hidden-row');

            $('.toggle-btn')
                .addClass('expanded')
                .removeClass('collapsed')
                .css('transform', 'rotate(90deg)'); 
            
            //hide all debit and credit header
            $('.debit-cell, .credit-cell').hide();
        }


        function restoreExpandedState() {
            // Restore previously expanded groups
            expandedGroups.forEach(function(groupId) {
                expandGroup(groupId, false); // false = don't add to Set again
            });
        }

        function expandGroup(groupId, updateSet = true) {
            const $toggleBtn = $(`.toggle-btn[data-target="${groupId}"]`);
            const $childRows = $(`.parent-${groupId}`);

            // Show child rows with animation
            $childRows.removeClass('hidden-row');

            // Update toggle button state
            $toggleBtn.removeClass('collapsed').addClass('expanded');
            $toggleBtn.css('transform', 'rotate(90deg)'); // ▼

            // Track expanded state
            if (updateSet) {
                expandedGroups.add(groupId);
            }
            $toggleBtn.closest('tr').find('.debit-cell, .credit-cell').hide();
        }

        function collapseGroup(groupId, updateSet = true) {
            const $toggleBtn = $(`.toggle-btn[data-target="${groupId}"]`);
            const $childRows = $(`.parent-${groupId}`);

            // Hide child rows
            $childRows.addClass('hidden-row');

            // Update toggle button state
            $toggleBtn.removeClass('expanded').addClass('collapsed');
            $toggleBtn.css('transform', 'rotate(0deg)'); // ▶

            // Remove from expanded state
            if (updateSet) {
                expandedGroups.delete(groupId);
            }
            $toggleBtn.closest('tr').find('.debit-cell, .credit-cell').show();
        }

        // Toggle group expansion on click
        $(document).on('click', '.toggle-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const groupId = $(this).data('target');
            const $row = $(this).closest('tr'); // current row
            const rowData = $('#trial-balance-table').DataTable().row($row).data(); // row data
            const isExpanded = $(this).hasClass('expanded');

            if (isExpanded) {
                collapseGroup(groupId);
                $row.find('.debit-cell, .credit-cell').show(); 
            } else {
                expandGroup(groupId);
                $row.find('.debit-cell, .credit-cell').hide(); 
            }
        });



        // Expand all groups
        $('#expand-all').on('click', function() {
            $('.toggle-btn').each(function() {
                const groupId = $(this).data('target');
                expandGroup(groupId);
            });
        });

        // Collapse all groups
        $('#collapse-all').on('click', function() {
            $('.toggle-btn').each(function() {
                const groupId = $(this).data('target');
                collapseGroup(groupId);
            });
        });

        const showTable = () => {
            trialBalanceTable.draw(false);
        }

        // Trigger reload when dropdowns change
        $('#filter-subtype, #filter-type').on('change', showTable);

        // Reset filters
        $('#reset-filters').click(function() {
            // Reset date range
            $('#datatableRange').val('');
            $('#datatableRange').data('daterangepicker').setStartDate(moment().startOf('year'));
            $('#datatableRange').data('daterangepicker').setEndDate(moment());
            $('#date-range-display').text('');

            // Reset dropdowns
            $('#filter-subtype').val('');
            $('#filter-type').val('');

            // Clear expanded groups
            expandedGroups.clear();

            // Reload table
            showTable();
        });

        // Date Range Picker initialization
        $('#datatableRange').daterangepicker({
            locale: daterangeLocale,
            linkedCalendars: false,
            startDate: moment().startOf('year'),
            endDate: moment(),
            ranges: daterangeConfig
        });

        // Handle date range selection
        $('#datatableRange').on('apply.daterangepicker', function(ev, picker) {
            $('#date-range-display').text(
                picker.startDate.format('MMM DD, YYYY') + ' - ' + picker.endDate.format('MMM DD, YYYY')
            );
            showTable();
        });

        // Initialize default date range display
        $('#date-range-display').text(
            moment().startOf('year').format('MMM DD, YYYY') + ' - ' + moment().format('MMM DD, YYYY')
        );

        // Enhanced row hover effects
        $(document).on('mouseenter', '#trial-balance-table tbody tr', function() {
            $(this).addClass('table-hover-effect');
        }).on('mouseleave', '#trial-balance-table tbody tr', function() {
            $(this).removeClass('table-hover-effect');
        });

        // Double-click to toggle (alternative interaction)
        $(document).on('dblclick', '.account-header', function() {
            const $toggleBtn = $(this).find('.toggle-btn');
            if ($toggleBtn.length) {
                $toggleBtn.trigger('click');
            }
        });

        // Keyboard navigation support
        $(document).on('keydown', function(e) {            
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'e':
                    case 'E':
                        e.preventDefault();
                        $('#expand-all').trigger('click');
                        break;
                    case 'c':
                    case 'C':
                        e.preventDefault();
                        $('#collapse-all').trigger('click');
                        break;
                }
            }
        });

        // Context menu for additional options (right-click)
        $(document).on('contextmenu', '.account-header', function(e) {
            e.preventDefault();
            const $row = $(this);
            const $toggleBtn = $row.find('.toggle-btn');
            const groupId = $toggleBtn.data('target');
            const isExpanded = $toggleBtn.hasClass('expanded');

            // Simple context action - toggle expansion
            if (isExpanded) {
                collapseGroup(groupId);
            } else {
                expandGroup(groupId);
            }
        });

        // Export functionality enhancement
        $(document).on('click', '.dt-button', function() {
            // Temporarily expand all groups for complete export
            const wasCollapsed = [];
            $('.toggle-btn.collapsed').each(function() {
                const groupId = $(this).data('target');
                wasCollapsed.push(groupId);
                expandGroup(groupId, false);
            });

            // After a short delay, restore the previous state
            setTimeout(function() {
                wasCollapsed.forEach(function(groupId) {
                    collapseGroup(groupId, false);
                });
            }, 1000);
        });

        // Responsive mobile handling
        function handleMobileView() {
            const isMobile = $(window).width() < 768;

            if (isMobile) {
                // On mobile, show more compact view
                $('#trial-balance-table').addClass('mobile-view');

                // Auto-collapse all on mobile for better performance
                if ($('.toggle-btn.expanded').length > 2) {
                    $('#collapse-all').trigger('click');
                }
            } else {
                $('#trial-balance-table').removeClass('mobile-view');
            }
        }

        // Handle window resize
        $(window).on('resize', function() {
            handleMobileView();
        });

        // Initial mobile check
        handleMobileView();
    </script>

    <style>
        /* Additional mobile styles */
        @media (max-width: 767px) {
            .mobile-view .account-detail td {
                font-size: 12px !important;
                padding: 4px 2px 4px 15px !important;
            }

            .mobile-view .toggle-btn {
                margin-right: 2px;
            }

            .mobile-view .indent-spacer {
                width: 10px;
            }

            .expand-collapse-controls {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 5px;
            }

            .expand-collapse-controls .btn {
                font-size: 11px;
                padding: 4px 8px;
                width: 100px;
            }
        }

        /* Hover effect enhancement */
        .table-hover-effect {
            background-color: #f8f9fa !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Loading animation for row transitions */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .trial-balance-table tbody tr:not(.hidden-row) {
            animation: fadeInUp 0.3s ease-out;
        }

        /* Print styles */
        @media print {

            .toggle-btn,
            .dt-buttons,
            .filter-section {
                display: none !important;
            }

            .trial-balance-table tbody tr.hidden-row {
                display: table-row !important;
            }

            .content-wrapper {
                padding: 0 !important;
            }
        }
    </style>
@endpush

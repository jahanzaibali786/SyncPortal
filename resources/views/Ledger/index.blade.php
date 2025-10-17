@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush
@section('filter-section')
 <style>
        /* .dt-buttons {
            position: fixed !important;
            top: 9% !important;
            right: 10px !important;
            z-index: 1000 !important;
        } */
        .w-tables{
            box-shadow: none !important;
            border: 1px solid #ebebeb !important;
        }
        .content-wrapper {
            background: #fff !important;
            padding: 30px 100px 30px !important;
        }

        /* .dt-buttons {
            position: fixed !important;
            top: 9% !important;
            right: 10px !important;
            z-index: 1000 !important;
        } */

        .dt-buttons {
            padding-top: 1rem;
            padding-left: 1rem;
        }

        .table-scroll {
            max-height: 600px;
            overflow: auto;
            position: relative;
        }

        #ledger-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #ledger-table thead th {
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
        #ledger-table tbody tr {
            background-clip: padding-box;
            transition: all 0.2s ease;
        }

        #ledger-table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

    </style>
    <x-filters.filter-box>
        <form id="filter-form" class="w-100">
            <!-- DATE RANGE -->
            <div class="select-box d-flex pr-2 border-right-grey">
                <p class="mb-0 p-2 f-14 text-dark-grey">@lang('app.duration')</p>
                <div class="select-status d-flex">
                    <input type="text" class="form-control border-0 p-2 f-14"
                           id="datatableRange" placeholder="@lang('placeholders.dateRange')">
                </div>
            </div>

            <!-- ACCOUNT FILTER -->
            <div class="select-box d-flex pr-2 border-right-grey">
                <p class="mb-0 p-2 f-14 text-dark-grey">Account</p>
                <div class="select-status">
                    <select class="form-control pt-2 select-picker" id="filter-account" name="account_id">
                        <option value="all">All Accounts</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $account->id == $accountId ? 'selected' : '' }} >{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- RESET -->
            <div class="select-box d-flex py-1 px-2">
                <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
        </form>
    </x-filters.filter-box>
@endsection

@section('content')
<div class="content-wrapper">
    <h4 class="mb-3">Ledger Report</h4>

    <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
        {!! $dataTable->table(['class' => 'table table-hover border-0 w-100', 'id' => 'ledger-table']) !!}
    </div>
</div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#ledger-table').on('preXhr.dt', function(e, settings, data) {
            let dateRangePicker = $('#datatableRange').data('daterangepicker');
            let startDate = $('#datatableRange').val();

            if (startDate == '') {
                data['startDate'] = null;
                data['endDate'] = null;
            } else {
                data['startDate'] = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                data['endDate']   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            data['account_id'] = $('#filter-account').val();
        });

        const showTable = () => {
            window.LaravelDataTables["ledger-table"].draw(true);
        }

        $('#filter-account, #datatableRange').on('change', function() {
            $('#reset-filters').removeClass('d-none');
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });
    </script>
@endpush

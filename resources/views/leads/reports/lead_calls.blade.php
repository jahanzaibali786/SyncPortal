@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    @include('sections.datatable_css')

    <style>
        .preloader-container {
            display: none !important;
        }
    </style>
@endpush


@section('filter-section')


    <x-filters.filter-box>
        <div class="row p-20">
            <!-- Start Date -->
            <div class="col-md-4">
                <x-forms.datepicker fieldId="start_date" fieldName="start_date" fieldLabel="Start Date"
                    :fieldValue="$startDate" :fieldPlaceholder="__('placeholders.date')" />
            </div>

            <!-- End Date -->
            <div class="col-md-4">
                <x-forms.datepicker fieldId="end_date" fieldName="end_date" fieldLabel="End Date" :fieldValue="$endDate"
                    :fieldPlaceholder="__('placeholders.date')" />
            </div>

            <!-- Status -->
            <div class="col-md-4">
                <div class="form-group my-3">
                    <label for="status_filter" class="form-label mb-12 text-dark-grey">Status</label>
                    <select id="status_filter" class="form-control pt-2 pb-2" style="min-width: 200px; height: 40px;">
                        <option value="">All</option>
                        <option value="answered">Answered</option>
                        <option value="missed">Missed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>


            {{-- <div class="col-lg-2 align-items-end d-flex mt-1">
                <x-forms.button-primary id="filter-results" icon="search" class="mr-2">
                    @lang('app.apply')
                </x-forms.button-primary>

                <x-forms.button-secondary id="reset-filters" icon="times">
                    @lang('app.reset')
                </x-forms.button-secondary>
            </div> --}}

        </div>
    </x-filters.filter-box>
@endsection


@section('content')
    <div class="content-wrapper">
        <div class=" d-flex flex-column w-tables rounded mt-4 bg-white">
            {!! $dataTable->table(['class' => 'table table-bordered lead-calls-table table-hover w-100'], true) !!}
        </div>
    </div>
@endsection


@push('scripts')
    {{-- ✅ jQuery must be first (layout already has it) --}}

    {{-- ✅ 1. Load DataTables before anything that uses it --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    {{-- ✅ 2. Then load Yajra-generated scripts (they call $(...).DataTable) --}}
    {!! $dataTable->scripts() !!}

    {{-- ✅ 3. Then your custom filter + datepicker logic --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker3.min.css" />
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>

    <script>
        $(function () {
            // Initialize datepickers
            $('#start_date, #end_date').datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                autoclose: true
            })
                .on('changeDate', function () {
                    window.LaravelDataTables["lead-calls-table"].ajax.reload();
                });

            // ✅ Attach filters (date + status) to each AJAX request
            window.LaravelDataTables["lead-calls-table"].on('preXhr.dt', function (e, settings, data) {
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
                data.status = $('#status_filter').val(); // 👈 added line
            });

            // ✅ Auto reload when status changes
            $('#status_filter').on('change', function () {
                window.LaravelDataTables["lead-calls-table"].ajax.reload();
            });

            // Reset filters button
            $('#reset-filters').click(function () {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#status_filter').val(''); // 👈 reset status filter
                window.LaravelDataTables["lead-calls-table"].ajax.reload();
            });
        });
    </script>


@endpush
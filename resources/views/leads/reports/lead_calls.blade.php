@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    @include('sections.datatable_css')

    <style>
        .preloader-container {
            display: none !important;
        }

        table a {
            color: black;
        }
    </style>
@endpush


@section('filter-section')


    <x-filters.filter-box>
        <div class="row p-3 align-items-end">
            <!-- Start Date -->
            <div class="col-md-3 col-sm-6 mb-3" style="min-width: 171px;">
                <x-forms.datepicker fieldId="start_date" fieldName="start_date" fieldLabel="Start Date"
                    :fieldValue="$startDate" :fieldPlaceholder="__('placeholders.date')" />
            </div>

            <!-- End Date -->
            <div class="col-md-3 col-sm-6 mb-3" style="min-width: 171px;">
                <x-forms.datepicker fieldId="end_date" fieldName="end_date" fieldLabel="End Date" :fieldValue="$endDate"
                    :fieldPlaceholder="__('placeholders.date')" />
            </div>

            <!-- Status -->
            @if($showStatusFilter)
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="form-group">
                        <label for="status_filter" class="form-label text-dark-grey">Status</label>
                        <select id="status_filter" class="form-control" style="height: 40px; min-width: 172px;">
                            <option value="">All</option>
                            <option value="200">Answered</option>
                            <option value="408">No Answer</option>
                            <option value="486">Busy</option>
                            <option value="503">Power Off</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- User -->
            @if($showUserFilter)
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="form-group">
                        <label for="user_filter" class="form-label text-dark-grey">User</label>
                        <select id="user_filter" class="form-control" style="height: 40px;">
                            <option value="">All Users</option>
                            @foreach(\App\Models\User::select('id', 'name')->orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>
    </x-filters.filter-box>


    <!-- 🌐 Universal DataTable Modal -->
    <div class="modal fade" id="universalDataModal" tabindex="-1" aria-labelledby="universalDataModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="universalDataModalLabel">Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="universal-data-table-container">
                        <table id="universalDataTable" class="table table-bordered table-hover w-100">
                            <thead>
                                <tr></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                data.status = $('#status_filter').val();
                data.user_id = $('#user_filter').val(); // ✅ new user filter
            });


            // ✅ Auto reload when status changes
            $('#status_filter').on('change', function () {
                window.LaravelDataTables["lead-calls-table"].ajax.reload();
            });

            $('#user_filter').on('change', function () {
                window.LaravelDataTables["lead-calls-table"].ajax.reload();
            });

            // Reset filters button
            $('#reset-filters').click(function () {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#status_filter').val('');
                $('#user_filter').val(''); // ✅ reset user filter
                window.LaravelDataTables["lead-calls-table"].ajax.reload();
            });


        });

        $(document).on('click', '.show-call-details, .show-modal-details', function (e) {
            e.preventDefault();

            const date = $(this).data('date');
            const type = $(this).data('type');
            const user = $(this).data('user');
            const title = $(this).data('title') || `Details (${user || date || ''})`;
            const ajaxUrl = window.LaravelDataTables["lead-calls-table"].ajax.url();
            const extraData = $(this).data('extra') || {};

            // ✅ Include same filters as main table
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const status = $('#status_filter').val();
            const userFilter = $('#user_filter').val();

            $('#universalDataModalLabel').text(title);
            $('#universalDataModal').modal('show');

            if ($.fn.DataTable.isDataTable('#universalDataTable')) {
                $('#universalDataTable').DataTable().destroy();
                $('#universalDataTable thead tr').empty();
            }

            $.ajax({
                url: ajaxUrl,
                data: Object.assign({
                    details: 1,
                    date: date,
                    type: type,
                    user: user,
                    start_date: startDate,
                    end_date: endDate,
                    status: status,
                    user_id: userFilter
                }, extraData),
                success: function (resp) {
                    if (!resp || !resp.data || resp.data.length === 0) {
                        $('#universalDataTable thead tr').html('<th>No data available</th>');
                        $('#universalDataTable tbody').html('');
                        return;
                    }

                    const firstRow = resp.data[0];
                    let keys = Object.keys(firstRow);
                    if (keys.includes('DT_RowIndex')) {
                        keys = ['DT_RowIndex', ...keys.filter(k => k !== 'DT_RowIndex')];
                    }

                    const columns = keys.map(key => ({ data: key, name: key }));
                    let headers = '';
                    keys.forEach(key => {
                        let title = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                        if (key === 'DT_RowIndex') title = '#';
                        headers += `<th>${title}</th>`;
                    });
                    $('#universalDataTable thead tr').html(headers);

                    $('#universalDataTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: ajaxUrl,
                            data: Object.assign({
                                details: 1,
                                date: date,
                                type: type,
                                user: user,
                                start_date: startDate,
                                end_date: endDate,
                                status: status,
                                user_id: userFilter
                            }, extraData)
                        },
                        columns: columns,
                        pageLength: 10,
                        responsive: true,
                        autoWidth: false
                    });
                },
                error: function (xhr) {
                    console.error('Modal Data Load Error:', xhr.responseText);
                    $('#universalDataTable thead tr').html('<th>Error loading data</th>');
                }
            });
        });



    </script>



@endpush
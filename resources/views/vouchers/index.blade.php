@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <form id="filter-form" class="w-100">
            <!-- DATE FILTER -->
            <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
                <div class="select-status d-flex">
                    <input type="text"
                        class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                        id="datatableRange" placeholder="@lang('placeholders.dateRange')">
                </div>
            </div>

            <!-- STATUS FILTER -->
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
                <div class="select-status">
                    <select class="form-control select-picker" id="filter-status" name="status">
                        <option value="all">@lang('app.all')</option>
                        <option value="draft">@lang('app.draft')</option>
                        <option value="posted">@lang('app.posted')</option>
                        <option value="voided">@lang('app.voided')</option>
                    </select>
                </div>
            </div>

            <!-- SEARCH -->
            <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </div>

            <!-- RESET -->
            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>
            {{-- //datatable prints buttons --}}
        </form>

    </x-filters.filter-box>
@endsection


@section('content')
    <style>
        .dt-buttons {
            position: fixed !important;
            top: 11% !important;
            right: 10px !important;
            z-index: 1 !important;
        }
    </style>
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                <x-forms.link-primary :link="route('vouchers.create')" class="mr-3 float-left openRightModal" icon="plus">
                    @lang('modules.vouchers.addVoucher')
                </x-forms.link-primary>
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="draft">@lang('app.draft')</option>
                        <option value="posted">@lang('app.posted')</option>
                        <option value="voided">@lang('app.voided')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>

        <!-- DataTable -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100', 'id' => 'vouchers-table']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#vouchers-table').on('preXhr.dt', function(e, settings, data) {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['status'] = $('#filter-status').val();
            data['searchText'] = $('#search-text-field').val();
        });

        const showTable = () => {
            window.LaravelDataTables["vouchers-table"].draw(true);
        }

        $('#filter-status, #search-text-field').on('change keyup', function() {
            $('#reset-filters').removeClass('d-none');
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.recoverRecord')",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "@lang('messages.confirmDelete')",
                    cancelButtonText: "@lang('app.cancel')",
                }).then((result) => {
                    if (result.isConfirmed) {
                        applyQuickAction();
                    }
                });
            } else {
                applyQuickAction();
            }
        });

        const applyQuickAction = () => {
            var rowdIds = $("#vouchers-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('vouchers.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };
        // delete-voucher
        function deleteVoucher(id) {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('vouchers.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        }
        $(document).on('click', '.delete-voucher', function() {
            var id = $(this).data('id');
            deleteVoucher(id);
        });
    </script>
@endpush

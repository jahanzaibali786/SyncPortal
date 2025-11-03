@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

@php
    // Safe defaults so this partial can be re-used
    $employees    = $employees    ?? collect();
    $departments  = $departments  ?? collect();
    $designations = $designations ?? collect();
    $roles        = $roles        ?? collect();
@endphp

<x-filters.filter-box>
    <!-- EMPLOYEE START -->
    <div class="select-box py-2 d-flex pr-2 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
        <div class="select-status">
            <select class="form-control select-picker" name="employee" id="employee" data-live-search="true" data-size="8">
                @if ($employees->count() > 1 || in_array('admin', user_roles()))
                    <option value="all">@lang('app.all')</option>
                @endif
                @foreach ($employees as $employee)
                    <x-user-option :user="$employee"/>
                @endforeach
            </select>
        </div>
    </div>
    <!-- EMPLOYEE END -->

    <!-- DESIGNATION (optional UI; requires backend support to filter) -->
    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
        <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.designation')</p>
        <div class="select-status">
            <select class="form-control select-picker" name="designation" id="designation">
                <option value="all">@lang('app.all')</option>
                @foreach ($designations as $designation)
                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- SEARCH -->
    <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
        <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
            <div class="input-group bg-grey rounded">
                <div class="input-group-prepend">
                    <span class="input-group-text border-0 bg-additional-grey">
                        <i class="fa fa-search f-13 text-dark-grey"></i>
                    </span>
                </div>
                <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                       placeholder="@lang('app.startTyping')">
            </div>
        </form>
    </div>

    <!-- RESET -->
    <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
        <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
            @lang('app.clearFilters')
        </x-forms.button-secondary>
    </div>

    <!-- MORE FILTERS -->
    <x-filters.more-filter-box>
        <!-- FROM DEPARTMENT -->
        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12">@lang('app.from') @lang('app.department')</label>
            <div class="select-filter mb-4">
                <div class="select-others">
                    <select class="form-control select-picker" name="from_department_id" id="from_department_id" data-container="body">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- TO DEPARTMENT -->
        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12">@lang('app.to') @lang('app.department')</label>
            <div class="select-filter mb-4">
                <div class="select-others">
                    <select class="form-control select-picker" name="to_department_id" id="to_department_id" data-container="body">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- The items below are purely UI unless you extend the query() (see note in step 3) -->
        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12">@lang('modules.employees.role')</label>
            <div class="select-filter mb-4">
                <div class="select-others">
                    <select class="form-control select-picker" name="role" id="role" data-container="body">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12">@lang('modules.employees.gender')</label>
            <div class="select-filter mb-4">
                <div class="select-others">
                    <select class="form-control select-picker" name="gender" id="gender" data-container="body">
                        <option value="all">@lang('app.all')</option>
                        <option value="male">@lang('app.male')</option>
                        <option value="female">@lang('app.female')</option>
                        <option value="others">@lang('app.others')</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="more-filter-items">
            <label class="f-14 text-dark-grey mb-12">@lang('modules.employees.employmentType')</label>
            <div class="select-filter mb-4">
                <div class="select-others">
                    <select class="form-control select-picker" name="employmentType" id="employmentType" data-container="body">
                        <option value="all">@lang('app.all')</option>
                        <option value="probation">@lang('app.onProbation')</option>
                        <option value="internship">@lang('app.onInternship')</option>
                        <option value="notice_period">@lang('app.onNoticePeriod')</option>
                        <option value="new_hires">@lang('app.newHires')</option>
                        <option value="long_standing">@lang('app.longStanding')</option>
                    </select>
                </div>
            </div>
        </div>
    </x-filters.more-filter-box>
</x-filters.filter-box>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between action-bar">
        <div>
            <x-forms.link-primary :link="route('transfers.create')" class="openRightModal" icon="plus">
                @lang('Add Transfer')
            </x-forms.link-primary>
        </div>
    </div>

    <div class="w-tables rounded bg-white mt-3">
        {!! $dataTable->table(['id' => 'employee-transfers-table', 'class' => 'table table-hover border-0 w-100']) !!}
    </div>
</div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    {!! $dataTable->scripts() !!}

<script>
const redraw = () => window.LaravelDataTables['employee-transfers-table']?.draw(true);

$('#employee-transfers-table').on('preXhr.dt', function (e, settings, data) {
    data['employee']            = $('#employee').val();
    data['searchText']          = $('#search-text-field').val();
    data['from_department_id']  = $('#from_department_id').val();
    data['to_department_id']    = $('#to_department_id').val();

    // The following are sent only if you later add backend support:
    data['designation']         = $('#designation').val();
    data['role']                = $('#role').val();
    data['gender']              = $('#gender').val();
    data['employmentType']      = $('#employmentType').val();
});

$('#employee, #from_department_id, #to_department_id, #designation, #role, #gender, #employmentType').on('change', redraw);
$('#search-text-field').on('keyup', redraw);

// Reset
$('#reset-filters').removeClass('d-none').on('click', function () {
    $('#employee').val('all');
    $('#search-text-field').val('');
    $('#from_department_id').val('all');
    $('#to_department_id').val('all');
    $('#designation').val('all');
    $('#role').val('all');
    $('#gender').val('all');
    $('#employmentType').val('all');
    $('.select-picker').selectpicker('refresh');
    redraw();
});

// Delete handler (your existing code)
$(document).on('click', '.delete-transfer', function () {
  const url = $(this).data('url');
  Swal.fire({
    title: "@lang('messages.sweetAlertTitle')",
    text: "@lang('messages.recoverRecord')",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: "@lang('messages.confirmDelete')",
    cancelButtonText: "@lang('app.cancel')",
    customClass: {confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary'},
    buttonsStyling: false
  }).then((result) => {
    if (result.isConfirmed) {
      $.easyAjax({
        type: 'POST',
        url: url,
        data: {_method: 'DELETE', _token: "{{ csrf_token() }}"},
        blockUI: true,
        success: function (response) {
          if (response.status === 'success' && window.LaravelDataTables?.['employee-transfers-table']) {
            window.LaravelDataTables['employee-transfers-table'].draw(false);
          }
        }
      });
    }
  });
});
</script>
@endpush

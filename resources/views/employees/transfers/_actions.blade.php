@php
/** @var \App\Models\EmployeeTransfer $row */
$editUrl   = route('transfers.edit', $row->id);
$deleteUrl = route('transfers.destroy', $row->id);
@endphp

<div class="task_view">
  <div class="dropdown">
    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle"
       href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="icon-options-vertical icons"></i>
    </a>

    <div class="dropdown-menu dropdown-menu-right">
      <a class="dropdown-item openRightModal" href="{{ $editUrl }}">
        <i class="fa fa-edit mr-2"></i>@lang('app.edit')
      </a>

      <a class="dropdown-item delete-transfer" href="javascript:;" data-url="{{ $deleteUrl }}">
        <i class="fa fa-trash mr-2"></i>@lang('app.delete')
      </a>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).off('click', '.delete-transfer').on('click', '.delete-transfer', function () {
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
          if (response.status === 'success' && typeof window.LaravelDataTables !== 'undefined') {
            const t = window.LaravelDataTables["employee-transfers-table"];
            if (t) t.draw(false);
          }
        }
      });
    }
  });
});
</script>
@endpush

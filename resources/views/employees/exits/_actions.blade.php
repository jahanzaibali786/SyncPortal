@php
    /** @var \App\Models\EmployeeExit $row */
    /** @var string $kind */
    $editRoute   = $kind==='termination' ? route('terminations.edit',$row)    : route('resignations.edit',$row);
    $deleteRoute = $kind==='termination' ? route('terminations.destroy',$row) : route('resignations.destroy',$row);
@endphp

<div class="task_view">
  <div class="dropdown">
    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" data-toggle="dropdown">
      <i class="icon-options-vertical icons"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
      <a class="dropdown-item openRightModal" href="{{ $editRoute }}">
        <i class="fa fa-edit mr-2"></i>@lang('app.edit')
      </a>
      <a class="dropdown-item delete-exit" href="javascript:;" data-url="{{ $deleteRoute }}">
        <i class="fa fa-trash mr-2"></i>@lang('app.delete')
      </a>
    </div>
  </div>
</div>

<script>
$('body').off('click','.delete-exit').on('click','.delete-exit',function(){
  const url = $(this).data('url');
  Swal.fire({
    title: "@lang('messages.sweetAlertTitle')",
    text: "@lang('messages.recoverRecord')",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: "@lang('messages.confirmDelete')",
    cancelButtonText: "@lang('app.cancel')"
  }).then((res)=>{
    if(res.isConfirmed){
      $.easyAjax({
        url,
        type:'POST',
        data:{ _method:'DELETE', _token:"{{ csrf_token() }}" },
        success: function(){
          window.LaravelDataTables['employee-exits-table'].draw(true);
        }
      });
    }
  });
});
</script>

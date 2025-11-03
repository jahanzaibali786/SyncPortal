{{-- resources/views/trips/_actions.blade.php --}}
@php
/** @var \App\Models\Trip $row */
$editUrl   = route('trips.edit', $row->id);
$deleteUrl = route('trips.destroy', $row->id);
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

      <a class="dropdown-item delete-trip" href="javascript:;" data-url="{{ $deleteUrl }}">
        <i class="fa fa-trash mr-2"></i>@lang('app.delete')
      </a>
    </div>
  </div>
</div>

<div class="modal-header">
    <h5 class="modal-title">
        @lang('modules.notifications.roleNotifications') - {{ ucfirst($role->name) }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">@lang('modules.notifications.availableNotifications')</h6>

        <x-forms.button-primary id="save-role-notifications">
            @lang('app.save')
        </x-forms.button-primary>
    </div>

    <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd;">
        <table class="table table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>@lang('app.module')</th>
                    <th>@lang('app.event')</th>
                    <th class="text-center">
                        <input type="checkbox" id="select-all-events">
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($events->groupBy('module') as $module => $group)
                    @foreach($group as $event)
                        @php
                            $setting = $settings->get($event->id);
                        @endphp
                        <tr>
                            <td>{{ ucfirst($module ?? 'General') }}</td>
                            <td>{{ $event->label }}</td>
                            <td class="text-center">
                                <input type="checkbox"
                                       class="toggle-role-event"
                                       data-event="{{ $event->id }}"
                                       {{ $setting && $setting->enabled ? 'checked' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    // ✅ Select all toggle
    $('body').on('change', '#select-all-events', function () {
        const checked = $(this).is(':checked');
        $('.toggle-role-event').prop('checked', checked);
    });

    // ✅ Save button — single AJAX call
    $('body').on('click', '#save-role-notifications', function () {
        const $btn = $(this);
        const roleId = "{{ $role->id }}";
        const selected = [];

        // Collect selected events
        $('.toggle-role-event').each(function () {
            selected.push({
                event_id: $(this).data('event'),
                enabled: $(this).is(':checked') ? 1 : 0
            });
        });

        // Disable button while saving
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.easyAjax({
            type: 'POST',
            url: "{{ route('role-notifications.store') }}",
            data: {
                _token: "{{ csrf_token() }}",
                role_id: roleId,
                settings: selected
            },
            blockUI: true,
            success: function (response) {
                if (response.status == 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message || 'Saved successfully',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    setTimeout(() => {
                        $('#myModal').modal('hide');
                        $('#roleNotificationsModal').modal('hide');
                    }, 800);
                }
            },
            complete: function () {
                // ✅ Re-enable button + unfreeze UI
                $btn.prop('disabled', false).html('@lang("app.save")');
                $.unblockUI(); // Manually unfreeze if easyAjax left it blocked
            },
            error: function () {
                // Handle error + unblock
                $.unblockUI();
                $btn.prop('disabled', false).html('@lang("app.save")');

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Something went wrong. Please try again.',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        });
    });
</script>



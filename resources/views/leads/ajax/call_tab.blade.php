@php
    $viewLeadCallPermission = user()->permission('view_lead_call') ?: 'all';
@endphp

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-call-tab">
    {{-- Call Table --}}
    <div class="d-flex flex-wrap p-20" id="call-list">
        @if ($viewLeadCallPermission == 'all' || $viewLeadCallPermission == 'added')
            <x-table headType="thead-light">
                <x-slot name="thead">
                    <th>@lang('modules.call.subject')</th>
                    <th>@lang('modules.call.callType')</th>
                    <th>@lang('modules.call.start')</th>
                    <th>@lang('modules.call.end')</th>
                    <th>@lang('modules.call.duration')</th>
                    <th>@lang('modules.call.recording')</th>
                    <th>@lang('modules.call.user')</th>
                    <th>@lang('app.action')</th>
                </x-slot>

                @forelse ($dealCalls as $call)
                    <tr id="call-row-{{ $call->id }}">
                        <td>{{ $call->subject ?? '—' }}</td>
                        <td>{{ ucfirst($call->call_type ?? '—') }}</td>
                        <td>{{ $call->start ? \Carbon\Carbon::parse($call->start)->format(company()->date_format . ' ' . company()->time_format) : '—' }}
                        </td>
                        <td>{{ $call->end ? \Carbon\Carbon::parse($call->end)->format(company()->date_format . ' ' . company()->time_format) : '—' }}
                        </td>
                        <td>{{ $call->duration ? $call->duration . ' mins' : '—' }}</td>
                        <td>
                            {{-- @if ($call->recording && Storage::exists('uploads/recording/' . $call->recording)) --}}
                            @if ($call->recording)
                                <audio controls>
                                    <source src="{{ Storage::disk('recordings')->url($call->recording) }}"
                                        type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            @else
                                <span class="text-muted">No Recording</span>
                            @endif
                        </td>
                        <td>{{ $call->user?->name ?? '—' }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-call" data-call-id="{{ $call->id }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-cards.no-record :message="__('messages.noRecordFound')" icon="phone" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        @endif
    </div>
</div>
<!-- TAB CONTENT END -->

@push('scripts')
    <script>
        $(document).on('click', '.delete-call', function() {
            var id = $(this).data('call-id');
            var url = "{{ route('lead-calls.destroy', ':id') }}".replace(':id', id);

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.deleteConfirmation')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "@lang('app.yesDeleteIt')"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.easyAjax({
                        type: 'DELETE',
                        url: url,
                        blockUI: true,
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#call-row-' + id).fadeOut();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush

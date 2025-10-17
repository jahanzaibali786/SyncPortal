@php
    $addLeadMeetingPermission = user()->permission('add_lead_meeting');
    $viewLeadMeetingPermission = user()->permission('view_lead_meeting');
    $editLeadMeetingPermission = user()->permission('edit_lead_meeting');
    $deleteLeadMeetingPermission = user()->permission('delete_lead_meeting');
@endphp

@php
    $addLeadMeetingPermission = $addLeadMeetingPermission ?: 'all';
    $viewLeadMeetingPermission = $viewLeadMeetingPermission ?: 'all';
@endphp

<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-meeting-tab">
    <div class="d-flex p-20">
        @if ($deal->leadStage->slug == 'win' || $deal->leadStage->slug == 'lost')
            <x-alert type="info" icon="info-circle">@lang('messages.cantAddMeeting')</x-alert>
        @endif

        @if (
            $deal->leadStage->slug != 'win' &&
                $deal->leadStage->slug != 'lost' &&
                ($addLeadMeetingPermission == 'all' || $addLeadMeetingPermission == 'added'))
            <div class="row">
                <div class="col-md-12">
                    <a class="f-15 f-w-500" href="javascript:;" id="add-lead-meeting">
                        <i class="icons icon-plus font-weight-bold mr-1"></i>
                        @lang('modules.meeting.newMeeting')
                    </a>
                </div>
            </div>
        @endif
    </div>

    <div class="d-flex flex-wrap pb-20 px-20" id="meeting-list">
        @if ($viewLeadMeetingPermission == 'all' || $viewLeadMeetingPermission == 'added')
            <x-table headType="thead-light">
                <x-slot name="thead">
                    <th>@lang('app.date')</th>
                    <th>@lang('app.time')</th>
                    <th>@lang('modules.meeting.minutes')</th>
                    <th>@lang('modules.meeting.joinUrl')</th>
                </x-slot>

                @forelse ($dealMeetings as $meeting)
                    <tr id="row-{{ $meeting->id }}">
                        <td>{{ \Carbon\Carbon::parse($meeting->meeting_date)->timezone(company()->timezone)->format(company()->date_format) }}
                        </td>
                        <td>
                            @php
                                try {
                                    $mt = \Carbon\Carbon::parse($meeting->meeting_time);
                                    $mtFormatted = $mt->format(company()->time_format);
                                } catch (\Exception $e) {
                                    $mtFormatted = $meeting->meeting_time;
                                }
                            @endphp
                            {{ $mtFormatted }}
                        </td>
                        <td>{{ $meeting->meeting_minutes ?? '—' }}</td>
                        <td>
                            @if ($meeting->join_url)
                                <a href="{{ $meeting->join_url }}" target="_blank" rel="noopener noreferrer">
                                    {{ $meeting->join_url }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-cards.no-record :message="__('messages.noRecordFound')" icon="calendar" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            $(document).on('click', '#add-lead-meeting', function() {
                var url = "{{ route('lead-meetings.create') }}?lead_id={{ $deal->id }}";
                $(MODAL_LG + ' ' + MODAL_HEADING).html("@lang('modules.meeting.newMeeting')");
                $.ajaxModal(MODAL_LG, url);
            });

            $(document).on('click', '.edit-meeting', function() {
                var id = $(this).data('meeting-id');
                var url = "{{ route('lead-meetings.edit', ':id') }}";
                url = url.replace(':id', id);
                $(MODAL_LG + ' ' + MODAL_HEADING).html("@lang('app.edit') @lang('modules.meeting.meeting')");
                $.ajaxModal(MODAL_LG, url);
            });

            $(document).on('click', '.delete-meeting', function() {
                var id = $(this).data('meeting-id');
                var url = "{{ route('lead-meetings.destroy', ':id') }}";
                url = url.replace(':id', id);

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
                                if (response.status === "success" || response.success) {
                                    $('#row-' + id).fadeOut();
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('meetingSaved', function() {
                $.easyAjax({
                    url: "{{ route('deals.show', $deal->id) }}?tab=meeting",
                    blockUI: true,
                    success: function(res) {
                        $('#deal-detail-panel').html(res.html);
                    }
                });
            });
        })();
    </script>
@endpush

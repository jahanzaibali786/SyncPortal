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
                ($addLeadMeetingPermission == 'all' || $addLeadMeetingPermission == 'added')
            )
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
                                <div class="d-flex gap-2">
                                    <a class="btn btn-primary" href="{{ $meeting->join_url }}" target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="fa fa-link"></i> join
                                    </a>
                                    <button type="button" class="btn btn-primary invite-btn" data-meeting-id="{{ $meeting->id }}"
                                        data-meeting-url="{{ $meeting->join_url }}"
                                        data-default-emails="{{ implode(',', array_filter([$meeting->email ?? null, $meeting->lead->client_email ?? null])) }}">
                                        <i class="fa fa-envelope mr-1"></i> Invite
                                    </button>

                                </div>
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

<!-- Invite Modal -->
<div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteModalLabel">Send Meeting Invites</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>

            </div>
            <div class="modal-body">
                <form id="inviteForm">
                    <input type="hidden" name="meeting_id" id="meeting_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Invite Emails</label>
                        <input id="emails" name="emails" placeholder="Type or paste emails and press Enter" />
                        <small class="text-muted d-block mt-1">
                            Default emails are prefilled. You can add or remove any.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <x-forms.button-cancel data-dismiss="modal"
                    class="btn-cancel border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
                
                <x-forms.button-primary id="sendInvitesBtn" class="btn-primary"
                        icon="paper-plane">Send Invites</x-forms.button-primary>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let input = document.querySelector('#emails');
            let tagify = new Tagify(input, {
                delimiters: ", ",
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                enforceWhitelist: false,
                dropdown: { enabled: 0 }
            });

            // When the Invite button is clicked
            $('.invite-btn').on('click', function () {
                const meetingId = $(this).data('meeting-id');
                const defaultEmails = $(this).data('default-emails')
                    ? $(this).data('default-emails').split(',').map(e => e.trim()).filter(e => e)
                    : [];

                $('#meeting_id').val(meetingId);

                // Clear previous tags
                tagify.removeAllTags();

                // Add meeting + lead emails as defaults
                if (defaultEmails.length > 0) {
                    tagify.addTags(defaultEmails);
                }

                $('#inviteModal').modal('show');
            });

            // Handle Send button click
            $('#sendInvitesBtn').on('click', function () {
                const meetingId = $('#meeting_id').val();
                const emails = tagify.value.map(tag => tag.value).join(',');

                $.ajax({
                    url: "{{ route('meetings.send-invites') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        meeting_id: meetingId,
                        emails: emails
                    },
                    beforeSend: function () {
                        $('#sendInvitesBtn').prop('disabled', true)
                            .html('<i class="fa fa-spinner fa-spin me-1"></i> Sending...');
                    },
                    success: function () {
                        $('#inviteModal').modal('hide');
                        // toastr.success('Invites sent successfully!');
                        $('#sendInvitesBtn').prop('disabled', false)
                            .html('<i class="fa fa-paper-plane me-1"></i> Send Invites');
                    },
                    error: function () {
                        console.error('Failed to send invites.');
                    },
                    complete: function () {
                        $('#sendInvitesBtn').prop('disabled', false)
                            .html('<i class="fa fa-paper-plane me-1"></i> Send Invites');
                    }
                });
            });
        });
    </script>


@endpush



{{-- @push('scripts') --}}
<script>
    (function () {
        $(document).on('click', '#add-lead-meeting', function () {
            var url = "{{ route('lead-meetings.create') }}?lead_id={{ $deal->id }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html("@lang('modules.meeting.newMeeting')");
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).on('click', '.edit-meeting', function () {
            var id = $(this).data('meeting-id');
            var url = "{{ route('lead-meetings.edit', ':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html("@lang('app.edit') @lang('modules.meeting.meeting')");
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).on('click', '.delete-meeting', function () {
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
                        success: function (response) {
                            if (response.status === "success" || response.success) {
                                $('#row-' + id).fadeOut();
                            }
                        }
                    });
                }
            });
        });

        $(document).on('meetingSaved', function () {
            $.easyAjax({
                url: "{{ route('deals.show', $deal->id) }}?tab=meeting",
                blockUI: true,
                success: function (res) {
                    $('#deal-detail-panel').html(res.html);
                }
            });
        });
    })();
</script>
{{-- @endpush --}}
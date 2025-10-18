@php
    $moveClass = '';
@endphp
@if ($draggable == 'false')
    @php
        $moveClass = 'move-disable';
    @endphp
@endif

@props(['lead', 'draggable' => 'true', 'allLabels' => collect()])

<div class="card rounded bg-white border-grey b-shadow-4 m-1 mb-2 {{ $moveClass }} task-card"
    data-task-id="{{ $lead->id }}" id="drag-task-{{ $lead->id }}">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between mb-2">
            <div class="d-flex justify-content-start gap-2 align-items-center">
                <input type="checkbox" class="deal-select-checkbox mr-2" value="{{ $lead->id }}">
                <a href="{{ route('deals.show', [$lead->id]) }}"
                    class="f-12 f-w-500 text-dark mb-0 text-wrap openRightModal mt-1">{{ $lead->name }}
                    @if (!is_null($lead->contact->client_id))
                        <i class="fa fa-check-circle text-success" data-toggle="tooltip"
                            data-original-title="@lang('modules.lead.convertedClient')"></i>
                    @endif
                </a>
            </div>
            <div class="d-flex align-items-center">
                @if (!is_null($lead->value))
                    <span class="ml-2 f-11 text-lightest">{{ currency_format($lead->value, $lead->currency_id) }}</span>
                @endif

                {{-- <button type="button" class="btn btn-sm btn-select-labels text-lightest p-0 ml-2"
                    data-lead-id="{{ $lead->id }}">
                    <i class="bi bi-three-dots-vertical"></i>
                </button> --}}
            </div>
        </div>

        {{-- @dd($lead->lead->labels) --}}

        <div class="mt-2">
            @if ($lead->labels->count() > 0)
                @foreach ($lead->labels as $label)
                    <span class="badge badge-{{ $label->label_color }} mr-1">
                        {{ $label->name }}
                    </span>
                @endforeach
            @endif
        </div>

        {{-- @dd($allLabels) --}}

        {{-- <div class="mt-2">
            @foreach ($allLabels as $label)
                <span class="badge badge-{{ $label->label_color }} mr-1">
                    {{ $label->name }}
                </span>
            @endforeach
        </div> --}}

        <!-- Add/Edit Labels button -->
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-light border btn-select-labels f-11"
                data-lead-id="{{ $lead->id }}">
                <i class="fa fa-tags text-primary"></i> Manage Labels
            </button>
        </div>



        @if ($lead->contact->client_name)
            <div class="d-flex mb-3 align-items-center mt-2">
                <i class="fa fa-building f-11 text-lightest"></i><span
                    class="ml-2 f-11 text-lightest">{{ $lead->contact->client_name_salutation }}</span>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-wrap align-items-center">

    {{-- Main Agent --}}
    @if (!is_null($lead->agent_id) && $lead->leadAgent && $lead->leadAgent->user)
        <div class="avatar-img mr-1 rounded-circle">
            <a href="{{ route('employees.show', $lead->leadAgent->user_id) }}"
                alt="{{ $lead->leadAgent->user->name }}"
                data-toggle="tooltip"
                data-original-title="{{ __('app.leadAgent') . ' : ' . $lead->leadAgent->user->name }}"
                data-placement="right">
                <img src="{{ $lead->leadAgent->user->image_url }}">
            </a>
        </div>
    @endif

    {{-- Sub Agents --}}
    @php
        $subAgentIds = $lead->sub_agents ? explode(',', $lead->sub_agents) : [];
        $subAgents = \App\Models\User::whereIn('id', $subAgentIds)->get();
    @endphp

    @foreach ($subAgents as $subAgent)
        <div class="avatar-img mr-1 rounded-circle">
            <a href="{{ route('employees.show', $subAgent->id) }}"
                alt="{{ $subAgent->name }}"
                data-toggle="tooltip"
                data-original-title="{{ __('modules.deal.subAgent') . ' : ' . $subAgent->name }}"
                data-placement="right">
                <img src="{{ $subAgent->image_url }}">
            </a>
        </div>
    @endforeach

</div>

            @if ($lead->next_follow_up_date != null && $lead->next_follow_up_date != '')
                <div class="d-flex text-lightest">
                    <span class="f-12 ml-1"><i class="f-11 bi bi-calendar"></i>
                        {{ \Carbon\Carbon::parse($lead->next_follow_up_date)->translatedFormat(company()->date_format) }}</span>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Modal (unique per card) --}}
<div class="modal fade" id="manageLabelsModal_{{ $lead->id }}" tabindex="-1" role="dialog"
    aria-labelledby="manageLabelsModalLabel_{{ $lead->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form class="manageLabelsForm" data-lead-id="{{ $lead->id }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="manageLabelsModalLabel_{{ $lead->id }}">Manage Labels</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="mb-2">Select Labels</label>
                        <div class="">
                            @foreach ($allLabels as $label)
                                <div class="form-check m-3 d-flex align-items-center" style="gap: 0.5rem;">
                                    <input class="form-check-input label-checkbox d-block" style="position: static;"
                                        type="checkbox" name="labels[]"
                                        id="label_{{ $lead->id }}_{{ $label->id }}"
                                        value="{{ $label->id }}"
                                        {{ $lead->labels->contains('id', $label->id) ? 'checked' : '' }}>
                                    <div class="form-check-label badge badge-{{ $label->label_color }} mt-1"
                                        for="label_{{ $lead->id }}_{{ $label->id }}"
                                        style="font-size: 0.9rem;">
                                        {{ $label->name }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('app.close')</button>
                    <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.btn-select-labels', function() {
        let leadId = $(this).data('lead-id');
        $('#manageLabelsModal_' + leadId).modal('show');
    });

    $(document).on('submit', '.manageLabelsForm', function(e) {
        e.preventDefault();
        let form = $(this);
        let leadId = form.data('lead-id');

        $.easyAjax({
            url: "{{ route('pipeline-labels.update-deal-labels') }}",
            type: "POST",
            data: form.serialize() + '&deal_id=' + leadId,
            blockUI: true,
            success: function(response) {
                if (response.status === "success") {
                    $('#manageLabelsModal_' + leadId).modal('hide');

                    // --- Frontend update ---
                    let badgeContainer = $('#drag-task-' + leadId + ' .mt-2').first();
                    badgeContainer.html(''); // Clear existing badges

                    if (response.labels && response.labels.length > 0) {
                        response.labels.forEach(function(label) {
                            badgeContainer.append(
                                `<span class="badge badge-${label.label_color} mr-1">${label.name}</span>`
                            );
                        });
                    }

                    // Optional toast/alert
                    // toastr.success('Labels updated successfully');
                }
            }
        });
    });
</script>





<!-- div end -->

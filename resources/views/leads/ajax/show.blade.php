@php
    $viewClientNote = user()->permission('view_deal_note');
    $viewProposalPermission = user()->permission('view_lead_proposals');
    $viewLeadFilePermission = user()->permission('view_lead_files');
    $viewLeadFollowupPermission = user()->permission('view_lead_follow_up');
@endphp

<div id="task-detail-section">
    <div class="row">
        <!--  MAIN CONTENT START - Changed to col-lg-7 for 7:5 ratio -->
        <div class="col-lg-8 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
            <x-cards.data :title="__('modules.deal.dealInfo')">
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                            <a class="dropdown-item openRightModal"
                                href="{{ route('deals.edit', $deal->id) . '?tab=overview' }}">@lang('app.edit')</a>
                            @if (
                                    $deleteLeadPermission == 'all'
                                    || ($deleteLeadPermission == 'added' && user()->id == $deal->added_by)
                                    || ($deleteLeadPermission == 'owned' && ((!is_null($deal->agent_id) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
                                    || ($deleteLeadPermission == 'both' && (((!is_null($deal->agent_id) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)) || user()->id == $deal->added_by))
                                )
                                            <a class="dropdown-item delete-table-row" href="javascript:;" data-id="{{ $deal->id }}">
                                                @lang('app.delete')
                                            </a>
                            @endif
                        </div>
                    </div>
                </x-slot>
                {{-- //labels --}}
                @php
                    $dealLabels = is_iterable($deal->dealLabels) ? $deal->dealLabels : [];
                @endphp
                <div class="deal-label-container mb-2" style="display:flex; flex-wrap:wrap; row-gap:2px;">
                    @if (count($dealLabels) > 0)
                        @foreach ($dealLabels as $label)
                            <span class="badge badge-{{ $label->label_color }} mr-1">{{ $label->name }}</span>
                        @endforeach
                    @endif
                </div>
                <p class="f-w-500">
                    <x-status style="color: {{ $deal->pipeline->label_color }}" color="yellow"
                                :value="$deal->pipeline->name"/>
                    <i class="bi bi-arrow-right mx-2"></i>
                    <x-status style="color: {{ $deal->leadStage->label_color }}" color="yellow"
                                :value="$deal->leadStage->name"/>
                </p>

                <x-cards.data-row :label="__('modules.deal.dealName')" :value="$deal->name ?? '--'"/>
                <x-cards.data-row :label="__('modules.leadContact.leadContact')"
                                    :value="$deal->contact->client_name_salutation ?? '--'"/>
                <x-cards.data-row :label="__('app.email')" :value="$deal->contact->client_email ?? '--'"/>
                <x-cards.data-row :label="__('modules.lead.companyName')"
                                    :value="!empty($deal->contact->company_name) ? $deal->contact->company_name : '--'"/>
                <x-cards.data-row :label="__('modules.deal.dealCategory')"
                                    :value="$deal->category->category_name ?? '--'"/>

                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block">
                        @lang('modules.deal.dealAgent')</p>
                    <p class="mb-0 text-dark-grey f-14">
                        @if (!is_null($deal->leadAgent))
                            <x-employee :user="$deal->leadAgent->user"/>
                        @else
                            --
                        @endif
                    </p>
                </div>

                {{-- Sub Agents --}}
                <div class="col-12 px-0 pb-3 d-flex align-items-start">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block">
                        @lang('modules.deal.subAgents')
                    </p>
                    <div class="mb-0 text-dark-grey f-14 d-flex flex-wrap">
                        @php
                            $subAgentIds = $deal->sub_agents ? explode(',', $deal->sub_agents) : [];
                            $subAgents = \App\Models\User::whereIn('id', $subAgentIds)->get();
                        @endphp

                        @if ($subAgents->count() > 0)
                            @foreach ($subAgents as $subAgent)
                                <div class="mr-2 mb-1">
                                    <x-employee :user="$subAgent"/>
                                </div>
                            @endforeach
                        @else
                            --
                        @endif
                    </div>
                </div>

                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block">{{ __('app.dealWatcher') }}</p>
                    <p class="mb-0 text-dark-grey f-14">
                        @if (!is_null($deal->dealWatcher))
                            <x-employee :user="$deal->dealWatcher"/>
                        @else
                            --
                        @endif
                    </p>
                </div>

                @if ($deal->leadStatus)
                    <div class="col-12 px-0 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block">@lang('app.status')</p>
                        <p class="mb-0 text-dark-grey f-14">
                            <x-status :value="$deal->leadStatus->type"
                                        :style="'color:' . $deal->leadStatus->label_color"/>
                        </p>
                    </div>
                @endif

                <x-cards.data-row :label="__('modules.deal.closeDate')"
                                    :value="($deal->close_date) ? $deal->close_date->translatedFormat(company()->date_format) : '--'"/>
                <x-cards.data-row :label="__('modules.deal.dealValue')"
                                    :value="($deal->value) ? currency_format($deal->value, $deal->currency_id) : '--'"/>
                <x-cards.data-row :label="__('modules.lead.products')"
                                    :value="($productNames) ? implode(', ', $productNames) : '--'"/>
                {{-- Custom fields data --}}
                <x-forms.custom-field-show :fields="$fields" :model="$deal"></x-forms.custom-field-show>
            </x-cards.data>

            <!-- Tabs Section -->
            <div class="bg-additional-grey rounded my-3">
                <div class="s-b-inner s-b-notifications bg-white b-shadow-4 rounded">
                    <x-tab-section class="deal-tabs">
                        @if($viewLeadFilePermission != 'none')
                            <x-tab-item class="ajax-tab files" :active="(request('tab') === 'files' || !request('tab'))"
                                            :link="route('deals.show', $deal->id) . '?tab=files'">@lang('modules.lead.file')</x-tab-item>
                        @endif
                        @if($viewLeadFollowupPermission != 'none')
                            <x-tab-item class="ajax-tab follow-up" :active="request('tab') === 'follow-up'"
                                            :link="route('deals.show', $deal->id) . '?tab=follow-up'">@lang('modules.lead.followUp')</x-tab-item>
                        @endif
                        @if($viewProposalPermission != 'none')
                            <x-tab-item class="ajax-tab proposals" :active="request('tab') === 'proposals'"
                                            :link="route('deals.show', $deal->id) . '?tab=proposals'">@lang('modules.lead.proposal')</x-tab-item>
                        @endif
                        @if ($viewClientNote != 'none')
                            <x-tab-item class="ajax-tab notes" :active="request('tab') === 'notes'"
                                            :link="route('deals.show', $deal->id) . '?tab=notes'">@lang('app.notes')</x-tab-item>
                        @endif
                        @if ($gdpr->enable_gdpr)
                            <x-tab-item class="ajax-tab gdpr" :active="request('tab') === 'gdpr'"
                                        :link="route('deals.show', $deal->id) . '?tab=gdpr'">@lang('app.menu.gdpr')</x-tab-item>
                        @endif
                        <x-tab-item class="ajax-tab history" :active="request('tab') === 'history'"
                                    :link="route('deals.show', $deal->id) . '?tab=history'">@lang('modules.tasks.history')</x-tab-item>
                        <x-tab-item class="ajax-tab meeting-tab"  
                            :active="request('tab') == 'meeting'"  
                            :link="route('deals.show', $deal->id) . '?tab=meeting'">
                            @lang('modules.meeting.meeting')
                        </x-tab-item>
                        <x-tab-item class="ajax-tab call-tab"
                            :active="request('tab') === 'call'"
                            :link="route('deals.show', $deal->id) . '?tab=call'">
                            @lang('modules.call.call')
                        </x-tab-item>
                    </x-tab-section>

                    <div class="s-b-n-content">
                        <div class="tab-content" id="nav-tabContent" style="max-height:400px !important; overflow-y:scroll;">
                            @include($tab)
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--  MAIN CONTENT END -->

        <!-- SIDEBAR START - Changed to col-lg-5 for 7:5 ratio -->
        <div class="col-lg-4 col-md-12">
            <!-- Contact Details Card -->
            <x-cards.data :title="__('modules.leadContact.leadDetails')" class="mb-4">
                <x-cards.data-row 
                    :label="__('modules.leadContact.leadContact')" 
                    otherClasses="mb-3" 
                    labelClasses="f-13 text-muted"
                    value="<a href='{{ route('lead-contact.show', $deal->contact->id) }}' class='text-darkest-grey f-14 font-weight-medium'> {{ $deal->contact->client_name_salutation }}</a>"
                />

                <x-cards.data-row 
                    :label="__('app.email')" 
                    :value="$deal->contact->client_email ?? '--'" 
                    otherClasses="mb-3" 
                    labelClasses="f-13 text-muted"
                />
                
                <x-cards.data-row 
                    :label="__('modules.lead.mobile')" 
                    :value="$deal->contact->mobile ?? '--'" 
                    otherClasses="mb-3" 
                    labelClasses="f-13 text-muted"
                />

                <x-cards.data-row 
                    :label="__('modules.lead.companyName')"
                    :value="!empty($deal->contact->company_name) ? $deal->contact->company_name : '--'" 
                    otherClasses="mb-4" 
                    labelClasses="f-13 text-muted"
                />

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap gap-2">
                    @if ($deal->contact->client_email)
                        <x-forms.link-secondary 
                            class="btn-sm mb-2" 
                            link='mailto:{{ $deal->contact->client_email }}'
                            icon="envelope">
                            @lang('app.email')
                        </x-forms.link-secondary>
                    @endif
                    
                    @if ($deal->contact->mobile)
                        <x-forms.button-secondary 
                            class="btn-copy call btn-sm mb-2" 
                            data-clipboard-text="{{ $deal->contact->mobile }}"
                            data-deal="{{ $deal->id }}"
                            data-contact="{{ $deal->contact->id }}"
                            data-user="{{ user()->id }}"
                            data-number="{{ $deal->contact->mobile }}"
                            icon="phone">
                            @lang('app.mobile')
                        </x-forms.button-secondary>
                    @endif
                </div>
            </x-cards.data>

            <!-- Description (Quill) -->
            <x-cards.data :title="__('modules.leadContact.description')" class="description-card">
                <form id="updateLeadNoteForm" class="mb-0">
                    @csrf
                    <input type="hidden" name="deal_id" value="{{ $deal->id }}">

                    <div class="form-group mb-3">
                        {{-- Quill editor container --}}
                        <div id="lead-note-quill" style="min-height: 150px;background:#fff;">
                        </div>

                        {{-- Hidden textarea to keep form compatibility / fallback --}}
                        <textarea name="note" id="lead-note-html" class="d-none"></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" id="update-lead-note-btn" class="btn btn-primary btn-sm px-4">
                            <i class="fa fa-save mr-1"></i> @lang('app.update')
                        </button>
                    </div>
                </form>
            </x-cards.data>
        </div>
        <!-- SIDEBAR END -->
    </div>

    <!-- Custom Styles for Better Design -->
    <style>
        .description-card .card-body {
            padding: 1.5rem;
        }
        
        /* keep Quill area visually similar to previous textarea */
        .ql-container {
            border: 1px solid #e9ecef;
            font-size: 14px;
            line-height: 1.5;
            min-height: 120px;
        }
        .ql-editor{
            min-height: 120px !important;
        }
        .ql-toolbar.ql-snow{
            background: #ECF0F5
        }
        .gap-2 > * {
            margin-right: 0.5rem;
        }
        
        .gap-2 > *:last-child {
            margin-right: 0;
        }
        
        @media (max-width: 991.98px) {
            .col-lg-7, .col-lg-5 {
                width: 100%;
                margin-bottom: 1rem;
            }
        }
        
        @media (min-width: 992px) {
            .col-lg-7 {
                flex: 0 0 58.333333%;
                max-width: 58.333333%;
            }
            
            .col-lg-5 {
                flex: 0 0 41.666667%;
                max-width: 41.666667%;
            }
        }
    </style>

    {{-- Quill CSS & JS (CDN). Remove and use local assets if you prefer. --}}
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>

    <script>
        (function () {
            // Ensure single source of truth for initial note HTML
            const initialNoteHtml = {!! json_encode($deal->lead->note ?? '') !!};

            // Initialize Quill
            const quillToolbarOptions = [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ indent: '-1' }, { indent: '+1' }],
                [{ align: [] }],
                ['link', 'image'],
                ['clean']
            ];

            const quill = new Quill('#lead-note-quill', {
                modules: {
                    toolbar: quillToolbarOptions
                },
                placeholder: "@lang('placeholders.description')",
                theme: 'snow'
            });

            // Load initial content safely
            if (initialNoteHtml && initialNoteHtml.trim().length) {
                // dangerouslyPasteHTML is ok here because content came from DB and was previously rendered
                quill.clipboard.dangerouslyPasteHTML(initialNoteHtml);
            } else {
                // If no content, ensure editor empty
                quill.setText('');
            }

            // Optional: if you have an image-handler helper, call it
            // if (typeof quillImageLoad === 'function') {
            //     try { quillImageLoad('#lead-note-quill'); } catch (e) { /* ignore */ }
            // }

            // Guarded call-button listener (if present)
            const callBtn = document.getElementById('callButton');
            if (callBtn) {
                callBtn.addEventListener('click', async function () {
                    const currentUserId = "{{ user()->id }}";
                    const dealId = "{{ $deal->id }}";
                    const payload = {
                        number: '+923001234567',
                        user_id: currentUserId ,
                        deal_id: dealId
                    };

                    try {
                        const res = await fetch('{{ route('call.trigger') }}', {
                            method: 'POST',
                            headers: {  
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (res.ok) {
                            alert('📞 Call triggered successfully!');
                            console.log(data);
                        } else {
                            alert('Error: ' + (data.error || 'Failed to trigger call'));
                        }
                    } catch (err) {
                        alert('Request failed: ' + err.message);
                    }
                });
            }

            // Submit handler: sends HTML to the server via easyAjax (keeps consistency)
            $(document).off('submit', '#updateLeadNoteForm').on('submit', '#updateLeadNoteForm', function (e) {
                e.preventDefault();

                const $btn = $('#update-lead-note-btn');
                $btn.prop('disabled', true);

                // Get HTML from Quill
                let noteContent = quill.root.innerHTML || '';
                // Quill produces "<p><br></p>" for empty content; normalize to empty string
                if (noteContent === '<p><br></p>' || noteContent.trim() === '') {
                    noteContent = '';
                }

                // put into hidden textarea for compatibility/backups (optional)
                $('#lead-note-html').val(noteContent);

                $.easyAjax({
                    url: "{{ route('deals.update-lead-note') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        deal_id: '{{ $deal->id }}',
                        note: noteContent
                    },
                    blockUI: true,
                    success: function (response) {
                        safeUnblockUI();
                        if (response && response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                text: response.message || '@lang("messages.updatedSuccessfully")',
                                toast: true,
                                position: 'top-end',
                                timer: 1400,
                                showConfirmButton: false
                            });

                            // Update quill with saved content (in case server modified)
                            if (response.note_html) {
                                try {
                                    quill.clipboard.dangerouslyPasteHTML(response.note_html);
                                    $('#lead-note-html').val(response.note_html);
                                } catch (err) {
                                    // fallback
                                    console.warn('Failed to set updated note from response:', err);
                                }
                            }
                        } else {
                            safeUnblockUI();
                            const message = (response && response.message) ? response.message : 'Failed to update note';
                            Swal.fire({
                                icon: 'error',
                                text: message,
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function (xhr) {
                        safeUnblockUI();
                        let msg = 'Server error';
                        if (xhr && xhr.responseJSON) {
                            if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            else if (xhr.responseJSON.errors) {
                                const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                if (firstKey) msg = xhr.responseJSON.errors[firstKey][0];
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            text: msg,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        safeUnblockUI();
                    }
                });
            });
            function safeUnblockUI() {
    // If blockUI plugin present
    if (typeof $.unblockUI === 'function') {
        try { $.unblockUI(); } catch(e){ /* ignore */ }
    }
    // Common overlay class names used by custom wrappers
    $('.blockUI, .block-ui, .blockOverlay, .block-ui-overlay, .overlay').remove();
    // remove any inline overflow hidden if applied
    $('body').css('overflow', '');
}

            // existing handlers (tabs, file actions, call actions etc.) remain as-is below...
            // (I kept the rest of your JS intact in other script blocks; if you want them merged here I can do that.)

        })();

    </script>

    <script>
        // Other page JS that you had below (kept largely intact)
        document.querySelectorAll(".call").forEach(function(button) {
            button.addEventListener("click", function() {
                let dealId = "{{ $deal->id }}";
                let userId = "{{ user()->id }}";
                let contactId = "{{ $deal->contact->id }}";
                let number = "{{ $deal->contact->mobile ?? '' }}";

                number = number.replace(/\s+/g, '');
                console.log('Calling number:', number, contactId, userId, dealId);

                fetch("http://localhost:5000/call", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            extension: number,
                            deal_id: dealId,
                            contact_id: contactId,
                            user_id: userId
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw new Error(err.error ||
                                    `HTTP error! Status: ${response.status}`);
                            });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        console.error("Fetch Error:", error.message);
                        Swal.fire({
                            icon: 'error',
                            text: 'Failed to initiate call: ' + error.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    });
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $(".ajax-tab").click(function (event) {
                event.preventDefault();

                $('.deal-tabs .ajax-tab').removeClass('active');
                $(this).addClass('active');

                const requestUrl = this.href;

                $.easyAjax({
                    url: requestUrl,
                    blockUI: true,
                    container: "#nav-tabContent",
                    historyPush: ($(RIGHT_MODAL).hasClass('in') ? false : true),
                    data: {
                        'json': true
                    },
                    success: function (response) {
                        if (response.status == "success") {
                            $('#nav-tabContent').html(response.html);
                        }
                    }
                });
            });
        });
    </script>

    <script>
        var fileLayout = 'thumbnail-list';
        function leadFilesView(layout) {
            $('#layout').html('');
            var leadID = "{{ $deal->id }}";
            fileLayout = layout;
            $.easyAjax({
                type: 'GET',
                url: "{{ route('deal-files.layout') }}",
                disableButton: true,
                blockUI: true,
                data: {
                    id: leadID,
                    layout: layout
                },
                success: function(response) {
                    $('#layout').html(response.html);
                    if (layout == 'gridview') {
                        $('#list-tabs').removeClass('btn-active');
                        $('#thumbnail').addClass('btn-active');
                    } else {
                        $('#list-tabs').addClass('btn-active');
                        $('#thumbnail').removeClass('btn-active');
                    }
                }
            });
        }


        // File tab scripts
        $('body').on('click', '.delete-lead-file', function() {
            var id = $(this).data('file-id');
            var deleteView = $(this).data('pk');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeFileText')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('deals.destroy', ':id') }}";
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
                                let dealsIndexUrl = "{{ route('deals.index') }}";
                                window.location.href = dealsIndexUrl;
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '#add-files', function() {
            const url = "{{ route('deal-files.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        // FIle tab scripts end

        // Follow up tab script start
        $('body').on('click', '#add-lead-followup', function() {
            const url = "{{ route('deals.follow_up', $deal->id) }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        $('body').on('click', '.edit-table-row-lead', function() {
            var id = $(this).data('followup-id');
            var url = "{{ route('deals.follow_up_edit', ':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.delete-table-row-lead', function() {
            var id = $(this).data('followup-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('deals.follow_up_delete', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                location.reload();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('deals.destroy', ':id') }}";
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
                                let dealsIndexUrl = "{{ route('deals.index') }}";
                                window.location.href = dealsIndexUrl;
                            }
                        }
                    });
                }
            });
        });

        // Follow up tab script end

        // Notes tab scripts
        $('body').on('click', '.delete-note-lead', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('deal-notes.destroy', ':id') }}";
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
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

        // Proposal tab scripts
        $('body').on('click', '.delete-proposal-table-row', function() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var id = $(this).data('proposal-id');
                    var url = "{{ route('proposals.destroy', ':id') }}";
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
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.sendButton', function() {
            var id = $(this).data('proposal-id');
            var url = "{{ route('proposals.send_proposal', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#invoices-table',
                blockUI: true,
                data: {
                    '_token': token
                },
                success: function(response) {
                    if (response.status == "success") {
                        window.location.reload();
                    }
                }
            });
        });

        $(document).on('click', '#add-lead-calls', function () {
            console.log('clicked');
            const leadId = "{{ $lead->id ?? $deal->id ?? '' }}"; // fallback safety
            const url = "{{ route('lead-calls.create-modal') }}" + "?lead_id=" + leadId;

            $(MODAL_LG + ' ' + MODAL_HEADING).html("@lang('modules.call.addCalls')");
            $.ajaxModal(MODAL_LG, url);
        });

        // $('body').on('click', '#add-files', function() {
        //     console.log('clicked');
        //     const url = "{{ route('deal-files.create') }}";
        //     $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        //     $.ajaxModal(MODAL_LG, url);
        // });
        // $('body').on('click', '#add-lead-followup', function() {
        //     console.log('clicked');
            
        //     $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        //     $.ajaxModal(MODAL_LG, url);
        // });
    </script>
</div>
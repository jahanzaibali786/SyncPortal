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
                                href="{{ route('deals.edit', $deal->id).'?tab=overview' }}">@lang('app.edit')</a>
                            @if (
                                $deleteLeadPermission == 'all'
                                || ($deleteLeadPermission == 'added' && user()->id == $deal->added_by)
                                || ($deleteLeadPermission == 'owned' && ((!is_null($deal->agent_id) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
                                || ($deleteLeadPermission == 'both' &&  (((!is_null($deal->agent_id) && user()->id == $deal->leadAgent->user->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)) || user()->id == $deal->added_by))
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
                                        :style="'color:'.$deal->leadStatus->label_color"/>
                        </p>
                    </div>
                @endif

                <x-cards.data-row :label="__('modules.deal.closeDate')"
                                    :value="($deal->close_date) ? $deal->close_date->translatedFormat(company()->date_format) : '--'"/>
                <x-cards.data-row :label="__('modules.deal.dealValue')"
                                    :value="($deal->value) ? currency_format($deal->value, $deal->currency_id) : '--'"/>
                <x-cards.data-row :label="__('modules.lead.products')"
                                    :value="($productNames) ? implode(', ' , $productNames) : '--'"/>

                {{-- Custom fields data --}}
                <x-forms.custom-field-show :fields="$fields" :model="$deal"></x-forms.custom-field-show>
            </x-cards.data>

            <!-- Tabs Section -->
            <div class="bg-additional-grey rounded my-3">
                <div class="s-b-inner s-b-notifications bg-white b-shadow-4 rounded">
                    <x-tab-section class="deal-tabs">
                        @if($viewLeadFilePermission != 'none')
                            <x-tab-item class="ajax-tab files" :active="(request('tab') === 'files' || !request('tab'))"
                                            :link="route('deals.show', $deal->id).'?tab=files'">@lang('modules.lead.file')</x-tab-item>
                        @endif
                        @if($viewLeadFollowupPermission != 'none')
                            <x-tab-item class="ajax-tab follow-up" :active="request('tab') === 'follow-up'"
                                            :link="route('deals.show', $deal->id).'?tab=follow-up'">@lang('modules.lead.followUp')</x-tab-item>
                        @endif
                        @if($viewProposalPermission != 'none')
                            <x-tab-item class="ajax-tab proposals" :active="request('tab') === 'proposals'"
                                            :link="route('deals.show', $deal->id).'?tab=proposals'">@lang('modules.lead.proposal')</x-tab-item>
                        @endif
                        @if ($viewClientNote != 'none')
                            <x-tab-item class="ajax-tab notes" :active="request('tab') === 'notes'"
                                            :link="route('deals.show', $deal->id).'?tab=notes'">@lang('app.notes')</x-tab-item>
                        @endif
                        @if ($gdpr->enable_gdpr)
                            <x-tab-item class="ajax-tab gdpr" :active="request('tab') === 'gdpr'"
                                        :link="route('deals.show', $deal->id).'?tab=gdpr'">@lang('app.menu.gdpr')</x-tab-item>
                        @endif
                        <x-tab-item class="ajax-tab history" :active="request('tab') === 'history'"
                                    :link="route('deals.show', $deal->id).'?tab=history'">@lang('modules.tasks.history')</x-tab-item>
                        <x-tab-item class="ajax-tab meeting-tab"  
                            :active="request('tab') == 'meeting'"  
                            :link="route('deals.show', $deal->id).'?tab=meeting'">
                            @lang('modules.meeting.meeting')
                        </x-tab-item>
                        <x-tab-item class="ajax-tab call-tab"
                            :active="request('tab') === 'call'"
                            :link="route('deals.show', $deal->id).'?tab=call'">
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
            <x-cards.data :title="__('modules.leadContact.description')" class="description-card">
                <form id="updateLeadNoteForm" class="mb-0">
                    @csrf
                    <input type="hidden" name="deal_id" value="{{ $deal->id }}">

                    <div class="form-group mb-3">
                        <textarea 
                            name="note" 
                            id="lead-note-editor" 
                            rows="8" 
                            class="form-control border-grey"
                            placeholder="@lang('placeholders.description')"
                            style="resize: vertical; min-height: 120px;"
                        >{!! $deal->lead->note ?? '' !!}</textarea>
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
        
        .description-card textarea {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .description-card textarea:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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

    <!-- Scripts remain the same -->
    <script>
    (function () {
        const TEXTAREA_ID = 'lead-note-editor';
        const MAX_RETRIES = 25;
        const RETRY_DELAY = 200;

        function loadCkeditorIfNeeded(callback) {
            if (window.CKEDITOR) {
                return callback();
            }

            const existing = document.querySelector('script[data-ckeditor-loader]');
            if (!existing) {
                const s = document.createElement('script');
                s.src = 'https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js';
                s.async = true;
                s.setAttribute('data-ckeditor-loader', '1');
                s.onload = function () {
                    callback();
                };
                s.onerror = function () {
                    console.error('Failed to load CKEditor script.');
                    callback();
                };
                document.head.appendChild(s);
            } else {
                let waitCount = 0;
                const wait = setInterval(function () {
                    if (window.CKEDITOR || ++waitCount > 50) {
                        clearInterval(wait);
                        callback();
                    }
                }, 100);
            }
        }

        function initCKEditorWithRetry(attempt = 0) {
            const textarea = document.getElementById(TEXTAREA_ID);
            if (!textarea) {
                if (attempt < MAX_RETRIES) {
                    setTimeout(() => initCKEditorWithRetry(attempt + 1), RETRY_DELAY);
                }
                return;
            }

            if (!window.CKEDITOR) {
                if (attempt < MAX_RETRIES) {
                    setTimeout(() => initCKEditorWithRetry(attempt + 1), RETRY_DELAY);
                } else {
                    console.warn('CKEditor not available after retries, using fallback textarea.');
                }
                return;
            }

            try {
                if (CKEDITOR.instances[TEXTAREA_ID]) {
                    CKEDITOR.instances[TEXTAREA_ID].destroy(true);
                }
            } catch (err) {
                console.warn('Error destroying CKEditor instance:', err);
            }

            try {
                CKEDITOR.replace(TEXTAREA_ID, {
                    height: 150,
                    removePlugins: 'elementspath',
                    toolbarGroups: [
                        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
                        { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align'] },
                        { name: 'styles' },
                        { name: 'colors' },
                        { name: 'tools' }
                    ],
                });

                const inst = CKEDITOR.instances[TEXTAREA_ID];
                if (inst) {
                    inst.on('instanceReady', function () {
                        // Editor ready
                    });
                }
            } catch (err) {
                if (attempt < MAX_RETRIES) {
                    setTimeout(() => initCKEditorWithRetry(attempt + 1), RETRY_DELAY);
                } else {
                    console.error('Failed to initialize CKEditor after retries:', err);
                }
            }
        }

        loadCkeditorIfNeeded(function () {
            initCKEditorWithRetry();
        });

        $(document).off('submit', '#updateLeadNoteForm').on('submit', '#updateLeadNoteForm', function (e) {
            e.preventDefault();

            const $btn = $('#update-lead-note-btn');
            $btn.prop('disabled', true);

            let noteContent = '';

            if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances[TEXTAREA_ID]) {
                try {
                    noteContent = CKEDITOR.instances[TEXTAREA_ID].getData();
                } catch (err) {
                    console.warn('Error reading CKEditor data, falling back to textarea:', err);
                    noteContent = $('#'+TEXTAREA_ID).val();
                }
            } else {
                noteContent = $('#'+TEXTAREA_ID).val();
            }

            $.ajax({
                url: "{{ route('deals.update-lead-note') }}",
                method: 'POST',
                dataType: 'json',
                data: {
                    _token: '{{ csrf_token() }}',
                    deal_id: '{{ $deal->id }}',
                    note: noteContent
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (response && response.status === 'success') {
                        //toast sawl
                        Swal.fire({
                            icon: 'success',
                            text: response.message || '@lang("messages.updatedSuccessfully")',
                            toast: true,
                            position: 'top-end',
                            timer: 1400,
                            showConfirmButton: false
                        });

                        if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances[TEXTAREA_ID]) {
                            try {
                                CKEDITOR.instances[TEXTAREA_ID].setData(noteContent);
                            } catch (err) {
                                console.warn('Error setting CKEditor data after save:', err);
                            }
                        } else {
                            $('#'+TEXTAREA_ID).val(noteContent);
                        }
                    } else {
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
                            text:msg,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false
                        });
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });
    })();
    </script>

    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>

    <script>
        document.querySelectorAll(".call").forEach(function(button) {
            button.addEventListener("click", function() {
                let dealId = "{{ $deal->id }}";
                let userId = "{{ user()->id }}";
                let contactId = "{{ $deal->contact->id }}";
                let number = "{{ $deal->contact->mobile }}";
                
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
    </script>
</div>
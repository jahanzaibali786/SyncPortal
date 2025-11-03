<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.call.addCalls')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true"><i class="fa fa-times"></i></span>
    </button>
</div>

<style>
    .form-group select {
        padding: 6px;
        height: 39px !important;
    }
</style>

<div class="modal-body">
    <div class="portlet-body">
        <x-form id="leadCallForm" method="POST" class="ajax-form" action="{{ route('lead-calls.store-modal') }}">
            <div class="form-body">
                <div class="row">
                    {{-- Lead name --}}
                    <div class="col-md-12">
                        <x-cards.data-row :label="__('modules.lead.clientName')" :value="$lead->company_name ?? ($lead->company_name ?? '--')" />
                    </div>

                    {{-- Subject --}}
                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('modules.call.subject')" fieldName="subject" fieldId="subject"
                            fieldRequired="true" :fieldPlaceholder="__('modules.call.subject')" />
                    </div>

                    {{-- To number --}}
                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('modules.call.phoneNumber')" fieldName="to_number"
                            fieldId="to_number" :fieldPlaceholder="__('modules.call.phoneNumber')" />
                    </div>

                    {{-- Call type --}}
                    <div class="col-md-6">
                        <x-forms.select fieldId="call_type" :fieldLabel="__('modules.call.callType')"
                            fieldName="call_type" fieldRequired="true">
                            <option value="inbound">@lang('modules.call.inbound')</option>
                            <option value="outbound">@lang('modules.call.outbound')</option>
                        </x-forms.select>
                    </div>

                    {{-- Call status --}}
                    <div class="col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('modules.call.status')" fieldName="status"
                            fieldRequired="true">
                            <option value="200">@lang('modules.call.answered')</option>
                            <option value="408">@lang('modules.call.noAnswer')</option>
                            <option value="486">@lang('modules.call.busy')</option>
                            <option value="503">@lang('modules.call.powerOff')</option>
                        </x-forms.select>
                    </div>

                    {{-- <div class="col-md-4">
                        <x-forms.text :fieldLabel="__('modules.call.duration')" fieldName="duration" fieldId="duration"
                            :fieldPlaceholder="0" />
                    </div> --}}

                    {{-- Start Time
                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 mt-3" for="start">@lang('modules.call.startTime')
                                <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control h-35 py-1" name="start" id="start"
                                required>
                        </div>
                    </div>

                    End Time
                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 mt-3" for="end">@lang('modules.call.endTime') <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control h-35 py-1" name="end" id="end" required>
                        </div>
                    </div> --}}

                    {{-- Call Date --}}
                    <div class="col-md-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 mt-3" for="call_date">@lang('modules.call.date') <span class="text-danger">*</span></label>
                            <input type="date" class="form-control h-35 py-1" name="call_date" id="call_date" required>
                        </div>
                    </div>

                    {{-- Start Time --}}
                    <div class="col-md-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 mt-3" for="start_time">@lang('modules.call.start') <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control h-35 py-1" name="start_time" id="start_time" required>
                        </div>
                    </div>

                    {{-- End Time --}}
                    <div class="col-md-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 mt-3" for="end_time">@lang('modules.call.end') <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control h-35 py-1" name="end_time" id="end_time" required>
                        </div>
                    </div>



                    {{-- Description --}}
                    <div class="col-md-12">
                        <x-forms.textarea :fieldLabel="__('modules.call.description')" fieldName="description"
                            fieldId="description" :fieldPlaceholder="__('modules.call.description')"
                            fieldRequired="false" />
                    </div>

                    {{-- Call result --}}
                    <div class="col-md-12">
                        <x-forms.textarea :fieldLabel="__('modules.call.result')" fieldName="call_result"
                            fieldId="call_result" :fieldPlaceholder="__('modules.call.result')" fieldRequired="false" />
                    </div>
                </div>
            </div>
            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
        </x-form>
    </div>
</div>

<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-lead-call" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

{{-- JS --}}
<script>
    $('#save-lead-call').click(function () {
        const form = $('#leadCallForm');
        const url = form.attr('action');

        $.easyAjax({
            url: url,
            container: '#leadCallForm',
            type: "POST",
            data: form.serialize(),
            blockUI: true,
            success: function (response) {
                if (response.status === 'success') {
                    $(MODAL_LG).modal('hide');
                    if (typeof window.reloadLeadCallsTable !== 'undefined') {
                        window.reloadLeadCallsTable();
                    }
                }
            }
        });
    });
</script>
@php
    // required vars: $kind, $employees
    // optional: $exit (for edit), $terminationTypes (for termination)
    $isEdit = isset($exit);
    $action = $isEdit
        ? ($kind==='termination' ? route('terminations.update', $exit) : route('resignations.update', $exit))
        : ($kind==='termination' ? route('terminations.store')        : route('resignations.store'));
    
    // Fallback labels if translation keys are missing
    $noticeLabel = __('app.noticeDate');
    if ($noticeLabel === 'app.noticeDate') { $noticeLabel = 'Notice Date'; }

    $terminationDateLabel = __('app.terminationDate');
    if ($terminationDateLabel === 'app.terminationDate') { $terminationDateLabel = 'Termination Date'; }

    $resignationDateLabel = __('app.resignationDate');
    if ($resignationDateLabel === 'app.resignationDate') { $resignationDateLabel = 'Resignation Date'; }
@endphp

<x-form id="save-exit" method="POST" class="ajax-form" :action="$action">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row p-20">
        <div class="col-md-6">
            <x-forms.select fieldId="employee_id" fieldName="employee_id"
                            :fieldLabel="__('app.employee')" search="true" required="true">
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}"
                        @selected($isEdit && (int)$exit->employee_id === (int)$emp->id)>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </x-forms.select>
        </div>

        <div class="col-md-6">
            <x-forms.datepicker fieldId="notice_date" fieldName="notice_date" class="custom-date-picker"
                                :fieldLabel="$noticeLabel"
                                :fieldValue="$isEdit && $exit->notice_date ? \Illuminate\Support\Carbon::parse($exit->notice_date)->format(company()->date_format) : null"/>
        </div>

        <div class="col-md-6">
            <x-forms.datepicker fieldId="effective_date" fieldName="effective_date" class="custom-date-picker"
                :fieldLabel="$kind==='termination' ? $terminationDateLabel : $resignationDateLabel"
                required="true"
                :fieldValue="$isEdit && $exit->effective_date ? \Illuminate\Support\Carbon::parse($exit->effective_date)->format(company()->date_format) : null"/>
        </div>

        @if($kind === 'termination')
        <div class="col-md-6">
            <x-forms.select fieldId="termination_type" fieldName="termination_type"
                            :fieldLabel="__('app.type')" required="true">
                @foreach(($terminationTypes ?? \App\Models\EmployeeExit::TERMINATION_TYPES) as $id=>$label)
                    <option value="{{ $id }}" @selected($isEdit && (int)$exit->termination_type===(int)$id)>
                        {{ $label }}
                    </option>
                @endforeach
            </x-forms.select>
        </div>
        @endif

        <div class="col-md-12">
            <x-forms.textarea fieldId="description" fieldName="description"
                              :fieldLabel="__('app.description')"
                              :fieldValue="$isEdit ? $exit->description : null"/>
        </div>
    </div>

    <x-form-actions>
        <x-forms.button-primary id="save-exit-btn" class="mr-3">
            @lang('app.save')
        </x-forms.button-primary>
        <x-forms.button-secondary class="border-0" data-dismiss="modal">
            @lang('app.cancel')
        </x-forms.button-secondary>
    </x-form-actions>
</x-form>

<script>
$(document).ready(function() {
    
    $('#save-exit-btn').click(function() {
        const url = $(this).closest('form').attr('action');
        const data = $('#save-exit').serialize();
        saveExit(data, url, "#save-exit-btn");
    });

    function saveExit(data, url, buttonSelector) {
        $.easyAjax({
            url: url,
            container: '#save-exit',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: buttonSelector,
            data: data,
            success: function(response) {
                if (response.status == 'success') {
                    if ($(MODAL_XL).hasClass('show')) {
                        $(MODAL_XL).modal('hide');
                        window.location.reload();
                    }
                    else {
                        window.location.href = response.redirectUrl;
                    }

                    if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                        showTable();
                    }
                }
            }
        });
    }

    $('.custom-date-picker').each(function(ind, el) {
        datepicker(el, {
            position: 'bl',
            ...datepickerConfig
        });
    });

    datepicker('#notice_date', {
        position: 'bl',
        ...datepickerConfig
    });

    datepicker('#effective_date', {
        position: 'bl',
        ...datepickerConfig
    });

    init(RIGHT_MODAL);
});
</script>

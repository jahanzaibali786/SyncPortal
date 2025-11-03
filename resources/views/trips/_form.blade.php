@php
    /** @var \App\Models\Trip|null $trip */
    $isEdit = isset($trip);
    $fmt    = company()->date_format;
    $start  = $isEdit && $trip?->start_date ? \Illuminate\Support\Carbon::parse($trip->start_date)->format($fmt) : null;
    $end    = $isEdit && $trip?->end_date   ? \Illuminate\Support\Carbon::parse($trip->end_date)->format($fmt)   : null;
@endphp

<div class="row p-20">
    <div class="col-md-6">
        <x-forms.select fieldId="employee_id" fieldName="employee_id" :fieldLabel="__('app.employee')" search="true" required="true">
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" @selected($isEdit && (int)$trip->employee_id === (int)$emp->id)>{{ $emp->name }}</option>
            @endforeach
        </x-forms.select>
    </div>

    <div class="col-md-6">
        <x-forms.text fieldId="place_of_visit" fieldName="place_of_visit" :fieldLabel="__('From Location')" :fieldValue="$isEdit ? $trip->place_of_visit : null" />
    </div>

    <div class="col-md-6">
        <x-forms.text fieldId="purpose_of_visit" fieldName="purpose_of_visit" :fieldLabel="__('To Location / Purpose')" :fieldValue="$isEdit ? $trip->purpose_of_visit : null" />
    </div>

    <div class="col-md-6">
        <x-forms.datepicker fieldId="start_date" fieldName="start_date" class="custom-date-picker" :fieldLabel="__('app.startDate')" :fieldValue="$start" required="true"/>
    </div>

    <div class="col-md-6">
        <x-forms.datepicker fieldId="end_date" fieldName="end_date" class="custom-date-picker" :fieldLabel="__('app.endDate')" :fieldValue="$end"/>
    </div>

    <div class="col-md-12">
        <x-forms.textarea fieldId="description" fieldName="description" :fieldLabel="__('app.description')" :fieldValue="$isEdit ? $trip->description : null" />
    </div>
</div>

<x-form-actions>
    <x-forms.button-primary id="save-trip-btn" class="mr-3">@lang('app.save')</x-forms.button-primary>
    <x-forms.button-secondary data-dismiss="modal">@lang('app.cancel')</x-forms.button-secondary>
</x-form-actions>

<script>
$(function () {
    // Click -> submit the closest ajax-form (create/edit)
    $(document).off('click', '#save-trip-btn').on('click', '#save-trip-btn', function(e){
        e.preventDefault();
        $(this).closest('form.ajax-form').trigger('submit');
    });

    // ajax submit handler
    $(document).off('submit', 'form.ajax-form').on('submit', 'form.ajax-form', function(e){
        e.preventDefault();
        const $form = $(this);
        $.easyAjax({
            url: $form.attr('action'),
            type: 'POST',
            container: '#' + $form.attr('id'),
            data: $form.serialize(),
            blockUI: true,
            disableButton: true,
            buttonSelector: '#save-trip-btn',
            success: function (resp) {
                if (resp.status === 'success') {
                    if ($(MODAL_XL).hasClass('show')) {
                        $(MODAL_XL).modal('hide');
                        window.location.reload();
                    } else if (resp.redirectUrl) {
                        window.location.href = resp.redirectUrl;
                    }
                }
            }
        });
    });

    // datepickers (same pattern as exits/transfers)
    $('.custom-date-picker').each(function(_, el){
        if (typeof datepicker === 'function') {
            datepicker(el, { position: 'bl', ...datepickerConfig });
        }
    });
    if (typeof datepicker === 'function') {
        datepicker('#start_date', { position: 'bl', ...datepickerConfig });
        datepicker('#end_date',   { position: 'bl', ...datepickerConfig });
    }
    if (typeof init === 'function') init(RIGHT_MODAL);
});
</script>

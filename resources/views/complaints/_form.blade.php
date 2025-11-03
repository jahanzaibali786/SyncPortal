@php
/** @var \App\Models\Complaint|null $complaint */
$isEdit  = isset($complaint);
$fmt     = company()->date_format ?? 'Y-m-d';
$dateVal = $isEdit && $complaint?->complaint_date
    ? \Illuminate\Support\Carbon::parse($complaint->complaint_date)->format($fmt)
    : null;
@endphp

<div class="row p-20">
    <div class="col-md-6">
        <x-forms.select fieldId="complaint_from" fieldName="complaint_from" :fieldLabel="__('From')" search="true" required="true">
            @foreach($users as $u)
                <option value="{{ $u->id }}" @selected($isEdit && (int)$complaint->complaint_from === (int)$u->id)>{{ $u->name }}</option>
            @endforeach
        </x-forms.select>
    </div>

    <div class="col-md-6">
        <x-forms.select fieldId="complaint_against" fieldName="complaint_against" :fieldLabel="__('Against')" search="true">
            <option value="">{{ __('app.select') }}</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" @selected($isEdit && (int)$complaint->complaint_against === (int)$u->id)>{{ $u->name }}</option>
            @endforeach
        </x-forms.select>
    </div>

    <div class="col-md-6">
        <x-forms.text fieldId="title" fieldName="title" :fieldLabel="__('Title')" :fieldValue="$isEdit ? $complaint->title : null" required="true"/>
    </div>

    <div class="col-md-6">
        <x-forms.datepicker fieldId="complaint_date" fieldName="complaint_date" class="custom-date-picker" :fieldLabel="__('Date')" :fieldValue="$dateVal" required="true"/>
    </div>

    <div class="col-md-6">
        <x-forms.select fieldId="status" fieldName="status" :fieldLabel="__('Status')" required="true">
            @php $status = $isEdit ? $complaint->status : 'pending'; @endphp
            <option value="pending" @selected($status==='pending')>Pending</option>
            <option value="in_progress" @selected($status==='in_progress')>In Progress</option>
            <option value="resolved" @selected($status==='resolved')>Resolved</option>
        </x-forms.select>
    </div>

    <div class="col-md-12">
        <x-forms.textarea fieldId="description" fieldName="description" :fieldLabel="__('app.description')" :fieldValue="$isEdit ? $complaint->description : null" />
    </div>
</div>

<x-form-actions>
    <x-forms.button-primary id="save-complaint-btn" class="mr-3">@lang('app.save')</x-forms.button-primary>
    <x-forms.button-secondary data-dismiss="modal">@lang('app.cancel')</x-forms.button-secondary>
</x-form-actions>

<script>
$(function () {
    $(document).off('click', '#save-complaint-btn').on('click', '#save-complaint-btn', function (e) {
        e.preventDefault();
        $(this).closest('form.ajax-form').trigger('submit');
    });

    $(document).off('submit', 'form.ajax-form').on('submit', 'form.ajax-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        $.easyAjax({
            url: $form.attr('action'),
            container: '#' + $form.attr('id'),
            type: 'POST',
            data: $form.serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: '#save-complaint-btn',
            success: function (response) {
                if (response.status === 'success') {
                    if ($(MODAL_XL).hasClass('show')) {
                        $(MODAL_XL).modal('hide');
                        window.location.reload();
                    } else if (response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    }
                    if (typeof showTable === 'function') showTable();
                }
            }
        });
    });

    $('.custom-date-picker').each(function(_, el) {
        if (typeof datepicker === 'function') {
            datepicker(el, { position: 'bl', ...datepickerConfig });
        }
    });
});
</script>

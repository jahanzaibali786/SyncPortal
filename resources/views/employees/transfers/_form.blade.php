@php
    /** @var \App\Models\EmployeeTransfer|null $transfer */
    $isEdit  = isset($transfer);
    $fmt     = company()->date_format;
    $value   = $isEdit && $transfer?->transfer_date
        ? \Illuminate\Support\Carbon::parse($transfer->transfer_date)->format($fmt)
        : null;
@endphp

<div class="row p-20">
    <div class="col-md-6">
        <x-forms.select fieldId="employee_id" fieldName="employee_id" :fieldLabel="__('app.employee')" search="true" required="true">
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" @selected($isEdit && (int)$transfer->employee_id === (int)$emp->id)>{{ $emp->name }}</option>
            @endforeach
        </x-forms.select>
    </div>

    <div class="col-md-6">
        <x-forms.datepicker
            fieldId="transfer_date"
            fieldName="transfer_date"
            class="custom-date-picker"
            :fieldLabel="__('app.date')"
            :fieldValue="$value"
            required="true" />
    </div>

    <div class="col-md-6">
        <x-forms.select fieldId="from_department_id" fieldName="from_department_id" :fieldLabel="__('app.from')" search="true">
            <option value="">{{ __('app.select') }}</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected($isEdit && (int)$transfer->from_department_id === (int)$dept->id)>{{ $dept->team_name }}</option>
            @endforeach
        </x-forms.select>
    </div>

    <div class="col-md-6">
        <x-forms.select fieldId="to_department_id" fieldName="to_department_id" :fieldLabel="__('app.to')" search="true">
            <option value="">{{ __('app.select') }}</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected($isEdit && (int)$transfer->to_department_id === (int)$dept->id)>{{ $dept->team_name }}</option>
            @endforeach
        </x-forms.select>
    </div>

    <div class="col-md-12">
        <x-forms.textarea fieldId="description" fieldName="description" :fieldLabel="__('app.description')"
            :fieldValue="$isEdit ? $transfer->description : null" />
    </div>
</div>

<x-form-actions>
    {{-- type="button" prevents native form submit; we submit via JS once --}}
    <x-forms.button-primary id="save-transfer-btn" class="mr-3" type="button">
        @lang('app.save')
    </x-forms.button-primary>
    <x-forms.button-secondary data-dismiss="modal">@lang('app.cancel')</x-forms.button-secondary>
</x-form-actions>

{{-- Own ALL JS here; create/edit views should NOT add extra submit handlers --}}
<script>
(function() {
  // Click => trigger the enclosing ajax-form submit (single path)
  $(document)
    .off('click.transfer', '#save-transfer-btn')
    .on('click.transfer', '#save-transfer-btn', function (e) {
      e.preventDefault();
      const $form = $(this).closest('form.ajax-form');
      if ($form.length) $form.trigger('submit');
    });

  // Submit handler for BOTH create (#save-transfer) and edit (#update-transfer)
  $(document)
    .off('submit.transfer', 'form.ajax-form')
    .on('submit.transfer', 'form.ajax-form', function (e) {
      e.preventDefault();

      const $form = $(this);

      // guard against double submit
      if ($form.data('submitting') === true) return;
      $form.data('submitting', true);

      const btn = $form.find('#save-transfer-btn');

      $.easyAjax({
        url: $form.attr('action'),
        container: '#' + $form.attr('id'),
        type: 'POST',
        data: $form.serialize(),
        disableButton: true,
        blockUI: true,
        buttonSelector: btn.length ? ('#' + btn.attr('id')) : undefined,
        success: function (response) {
          $form.data('submitting', false);

          if (response.status === 'success') {
            if (typeof showTable === 'function') showTable();

            if ($(MODAL_XL).length && $(MODAL_XL).hasClass('show')) {
              $(MODAL_XL).modal('hide');
              window.location.reload(); // refresh list after modal close
            } else if (response.redirectUrl) {
              window.location.href = response.redirectUrl;
            }
          }
        },
        error: function (xhr) {
          $form.data('submitting', false);

          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            const errs = xhr.responseJSON.errors;
            let html = '';
            Object.keys(errs).forEach(k => html += errs[k].join('<br>') + '<br>');
            if (typeof $.showToastr === 'function') $.showToastr(html, 'error');
            else alert(html);
          }
          console.error('Transfer save failed:', xhr.responseText || xhr.statusText);
        }
      });
    });

  // initialize datepicker once per element
  function initOneDatepicker(el) {
    const $el = $(el);
    if ($el.data('dp-init')) return;
    if (typeof datepicker === 'function') {
      datepicker(el, { position: 'bl', ...(window.datepickerConfig || {}) });
      $el.data('dp-init', true);
    }
  }
  $('.custom-date-picker').each(function(_, el){ initOneDatepicker(el); });
  initOneDatepicker('#transfer_date');

  // Worksuite helper (safe)
  if (typeof init === 'function' && typeof RIGHT_MODAL !== 'undefined') { init(RIGHT_MODAL); }
})();
</script>

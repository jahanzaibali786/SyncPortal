@php
    $manageTaxPermission = user()->permission('manage_tax');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.credit-notes.addTax')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createTax">
        <div class="row">
            <div class="col-lg-4 col-md-4">
                <x-forms.label class="my-3" fieldId="chart_account_id" :fieldLabel="__('modules.accounts.incomeAccount')">
                </x-forms.label>
                <x-forms.input-group>
                    <select class="form-control select-picker" name="chart_account_id" id="chart_account_id"
                        data-live-search="true">
                        <option value="">--</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </x-forms.input-group>
            </div>
            <div class="col-sm-4 col-lg-4">
                <x-forms.text fieldId="tax_name" :fieldLabel="__('modules.invoices.taxName')" fieldName="tax_name" fieldRequired="true"
                    fieldPlaceholder="">
                </x-forms.text>
            </div>
            <div class="col-sm-4 col-lg-4">
                <x-forms.text fieldId="rate_percent" :fieldLabel="__('modules.invoices.rate')" fieldName="rate_percent" fieldRequired="true"
                    fieldPlaceholder="">
                </x-forms.text>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-tax" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(document).ready(function() {
        $('.select-picker').selectpicker();
    });

    $('#save-tax').click(function() {
        var url = "{{ route('taxes.store') }}";
        $.easyAjax({
            url: url,
            container: '#createTax',
            type: "POST",
            data: $('#createTax').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });
</script>

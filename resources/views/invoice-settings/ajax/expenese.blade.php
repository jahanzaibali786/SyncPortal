<link rel="stylesheet" href="{{ asset('vendor/css/image-picker.min.css') }}">

<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    @method('POST')

    <div class="row">

        <div class="col-lg-6 col-md-6">
            <x-forms.label class="my-3" fieldId="expense_payable_account_id" :fieldLabel="__('modules.accounts.payableAccount')">
            </x-forms.label>
            <x-forms.input-group>
                <select class="form-control select-picker" name="expense_payable_account_id" id="expense_payable_account_id"
                    data-live-search="true">
                    <option value="">--</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @if ($account->id == $invoiceSetting->expense_payable_account_id) selected @endif>
                            {{ $account->name }}</option>
                    @endforeach
                </select>
            </x-forms.input-group>
        </div>
        
    </div>

</div>


<!-- Buttons Start -->
<div class="w-100 border-top-grey">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </x-setting-form-actions>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('invoice_settings.updateExpenseSetting') }}",
            container: '#editSettings',
            type: "POST",
            redirect: true,
            file: true,
            data: $('#editSettings').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-form",
        })
    });
</script>
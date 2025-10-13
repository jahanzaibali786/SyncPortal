<div class="row">
    <div class="col-sm-12">
        <x-form id="save-voucher-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('modules.vouchers.voucherDetails')
                </h4>

                <div class="row p-20">
                    <!-- Voucher Type -->
                    <div class="col-md-4">
                        <x-forms.select fieldId="voucher_type" fieldName="voucher_type" fieldRequired="true"
                            :fieldLabel="__('modules.vouchers.type')">
                            <option value="">--</option>
                            <option value="JV">@lang('modules.vouchers.jv')</option>
                            <option value="CPV">@lang('modules.vouchers.cpv')</option>
                            <option value="BPV">@lang('modules.vouchers.bpv')</option>
                            <option value="CRV">@lang('modules.vouchers.crv')</option>
                            <option value="BRV">@lang('modules.vouchers.brv')</option>
                        </x-forms.select>
                    </div>
                    <input type="hidden" name="company_id" value="{{ company()->id }}">

                    <!-- Voucher Number -->
                    <div class="col-md-4">
                        <x-forms.text fieldId="number" fieldName="number" fieldRequired="true" :fieldLabel="__('modules.vouchers.number')"
                            :fieldPlaceholder="__('placeholders.vouchers.number')" />
                    </div>

                    <!-- Voucher Date -->
                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="date" fieldRequired="true" :fieldLabel="__('app.date')" fieldName="date"
                            :fieldPlaceholder="__('placeholders.date')" :fieldValue="\Carbon\Carbon::today()->format(company()->date_format)" />
                    </div>

                    <!-- Payment Method -->
                    <div class="col-md-4">
                        <x-forms.select fieldId="payment_method" fieldName="payment_method" :fieldLabel="__('modules.payments.paymentMethod')">
                            <option value="">--</option>
                            <option value="cash">@lang('modules.payments.cash')</option>
                            <option value="bank">@lang('modules.payments.bank')</option>
                            <option value="cheque">@lang('modules.payments.cheque')</option>
                            <option value="card">@lang('modules.payments.card')</option>
                        </x-forms.select>
                    </div>

                    <!-- Reference Numbers -->
                    <div class="col-md-4">
                        <x-forms.text fieldId="check_number" fieldName="check_number" :fieldLabel="__('modules.vouchers.checkNumber')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="bank_reference" fieldName="bank_reference" :fieldLabel="__('modules.vouchers.bankReference')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="deposit_slip" fieldName="deposit_slip" :fieldLabel="__('modules.vouchers.depositSlip')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="cashier_info" fieldName="cashier_info" :fieldLabel="__('modules.vouchers.cashierInfo')" />
                    </div>

                    <!-- Memo -->
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="memo" fieldName="memo" :fieldLabel="__('app.description')"
                            fieldPlaceholder="Enter notes..." />
                    </div>
                </div>

                <!-- Journal Entry Lines -->
                <h5 class="p-20 border-top-grey">@lang('modules.vouchers.entryLines')</h5>
                <div class="row px-20 pb-20" id="journal-lines">
                    <div class="col-md-12">
                        <table class="table table-bordered" id="linesTable">
                            <thead>
                                <tr>
                                    <th>@lang('modules.accounts.account')</th>
                                    <th>@lang('modules.vouchers.debit')</th>
                                    <th>@lang('modules.vouchers.credit')</th>
                                    <th>@lang('app.note')</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="lines[0][account_id]" class="form-control select-picker"
                                            data-live-search="true" required>
                                            <option value="">--</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" style="height: revert-layer !important;" step="0.01" name="lines[0][debit]"
                                            class="form-control"></td>
                                    <td><input type="number" style="height: revert-layer !important;" step="0.01" name="lines[0][credit]"
                                            class="form-control"></td>
                                    <td><input type="text" style="height: revert-layer !important;" name="lines[0][memo]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-line">X</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-primary" id="addLine">@lang('app.add')
                            @lang('modules.vouchers.line')</button>
                    </div>
                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-voucher-form" class="mr-3"
                        icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('vouchers.index')"
                        class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        let lineIndex = 1;
        
        //voucher number fetch
        $('#voucher_type').change(function() {
            var type = $(this).val();
            if (type) {
                $.easyAjax({
                    url: "{{ route('vouchers.fetch_number') }}",
                    type: "GET",
                    data: {
                        type: type
                    },
                    success: function(response) {
                        $('#number').val(response.number);
                        $('#number').attr('readonly', true);
                    }
                });
            }
        });
        // Add line
        $('#addLine').click(function() {
            let row = `<tr>
            <td>
                <select name="lines[${lineIndex}][account_id]" class="form-control select-picker"
                    data-live-search="true" required>
                        <option value="">--</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" step="0.01" name="lines[${lineIndex}][debit]" class="form-control"></td>
                <td><input type="number" step="0.01" name="lines[${lineIndex}][credit]" class="form-control"></td>
                <td><input type="text" name="lines[${lineIndex}][memo]" class="form-control"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-line">X</button></td>
            </tr>`;
            $('#linesTable tbody').append(row);
            $('.select-picker').selectpicker('refresh');
            lineIndex++;
        });

        // Remove line
        $(document).on('click', '.remove-line', function() {
            $(this).closest('tr').remove();
        });

        // Save form
        $('#save-voucher-form').click(function() {
            const url = "{{ route('vouchers.store') }}";
            var data = $('#save-voucher-data-form').serialize();

            $.easyAjax({
                url: url,
                container: '#save-voucher-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-voucher-form",
                data: data,
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });
    });
</script>

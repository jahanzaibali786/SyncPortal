<div class="row">
    <div class="col-sm-12">
        <x-form id="update-voucher-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('modules.vouchers.editVoucher')
                </h4>

                <div class="row p-20">
                    <!-- Voucher Type -->
                    <div class="col-md-4">
                        <x-forms.select fieldId="voucher_type" fieldName="voucher_type" fieldRequired="true"
                            :fieldLabel="__('modules.vouchers.type')" disabled>
                            <option value="">--</option>
                            <option value="JV" {{ $voucher->voucher_type == 'JV' ? 'selected' : '' }}>@lang('modules.vouchers.jv')</option>
                            <option value="CPV" {{ $voucher->voucher_type == 'CPV' ? 'selected' : '' }}>@lang('modules.vouchers.cpv')</option>
                            <option value="BPV" {{ $voucher->voucher_type == 'BPV' ? 'selected' : '' }}>@lang('modules.vouchers.bpv')</option>
                            <option value="CRV" {{ $voucher->voucher_type == 'CRV' ? 'selected' : '' }}>@lang('modules.vouchers.crv')</option>
                            <option value="BRV" {{ $voucher->voucher_type == 'BRV' ? 'selected' : '' }}>@lang('modules.vouchers.brv')</option>
                        </x-forms.select>
                    </div>
                    <input type="hidden" name="voucher_type" value="{{ $voucher->voucher_type }}">
                    <input type="hidden" name="company_id" value="{{ company()->id }}">

                    <!-- Voucher Number -->
                    <div class="col-md-4">
                        <x-forms.text fieldId="number" fieldName="number" fieldRequired="true"
                            :fieldLabel="__('modules.vouchers.number')"
                            :fieldPlaceholder="__('placeholders.vouchers.number')"
                            :fieldValue="$voucher->number"
                            :fieldReadOnly="true"
                             />
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
                            <option value="cash" {{ $voucher->payment_method == 'cash' ? 'selected' : '' }}>@lang('modules.payments.cash')</option>
                            <option value="bank" {{ $voucher->payment_method == 'bank' ? 'selected' : '' }}>@lang('modules.payments.bank')</option>
                            <option value="cheque" {{ $voucher->payment_method == 'cheque' ? 'selected' : '' }}>@lang('modules.payments.cheque')</option>
                            <option value="card" {{ $voucher->payment_method == 'card' ? 'selected' : '' }}>@lang('modules.payments.card')</option>
                        </x-forms.select>
                    </div>

                    <!-- Reference Numbers -->
                    <div class="col-md-4">
                        <x-forms.text fieldId="check_number" fieldName="check_number" :fieldLabel="__('modules.vouchers.checkNumber')"
                            :fieldValue="$voucher->check_number" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="bank_reference" fieldName="bank_reference" :fieldLabel="__('modules.vouchers.bankReference')"
                            :fieldValue="$voucher->bank_reference" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="deposit_slip" fieldName="deposit_slip" :fieldLabel="__('modules.vouchers.depositSlip')"
                            :fieldValue="$voucher->deposit_slip" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="cashier_info" fieldName="cashier_info" :fieldLabel="__('modules.vouchers.cashierInfo')"
                            :fieldValue="$voucher->cashier_info" />
                    </div>

                    <!-- Memo -->
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="memo" fieldName="memo" :fieldLabel="__('app.description')"
                            fieldPlaceholder="Enter notes..." :fieldValue="$voucher->memo" />
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
                                @foreach ($voucher->lines as $index => $line)
                                    <tr>
                                        <td>
                                            <select name="lines[{{ $index }}][account_id]" class="form-control select-picker"
                                                data-live-search="true" required>
                                                <option value="">--</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}" {{ $line->chart_of_account_id == $account->id ? 'selected' : '' }}>
                                                        {{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" name="lines[{{ $index }}][debit]" class="form-control"
                                                value="{{ $line->debit }}"></td>
                                        <td><input type="number" step="0.01" name="lines[{{ $index }}][credit]" class="form-control"
                                                value="{{ $line->credit }}"></td>
                                        <td><input type="text" name="lines[{{ $index }}][memo]" class="form-control"
                                                value="{{ $line->memo }}"></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-line">X</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn m-4 btn-sm btn-primary" id="addLine">@lang('app.add')
                            @lang('modules.vouchers.line')
                        </button>
                    </div>
                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="update-voucher-form" class="mr-3"
                        icon="check">@lang('app.update')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('vouchers.index')"
                        class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        let lineIndex = {{ $voucher->lines->count() }};
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

        // Update form
        $('#update-voucher-form').click(function() {
            const url = "{{ route('vouchers.update', $voucher->id) }}";
            var data = $('#update-voucher-data-form').serialize();

            $.easyAjax({
                url: url,
                container: '#update-voucher-data-form',
                type: "PUT",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-voucher-form",
                data: data,
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });
    });
</script>

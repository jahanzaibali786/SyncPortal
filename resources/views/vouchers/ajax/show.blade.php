<div class="row">
    <div class="col-sm-12">
        <div class="add-client bg-white rounded">
            <div class="p-20 border-top-grey border-bottom-grey d-flex justify-content-between align-items-center">
                <h4 class="mb-0 f-21 font-weight-normal">
                    @lang('modules.vouchers.voucherDetails')
                </h4>   
                <x-forms.button-cancel :link="route('vouchers.index')" class="border-0">
                        @lang('app.back')
                </x-forms.button-cancel>
            </div>
            <div class="row p-20">
                <!-- Voucher Type -->
                <div class="col-md-4">
                    <x-forms.label fieldId="voucher_type" :fieldLabel="__('modules.vouchers.type')" />
                    <p class="form-control-static">{{ $voucher->voucher_type }}</p>
                </div>

                <!-- Voucher Number -->
                <div class="col-md-4">
                    <x-forms.label fieldId="number" :fieldLabel="__('modules.vouchers.number')" />
                    <p class="form-control-static">{{ $voucher->number }}</p>
                </div>

                <!-- Voucher Date -->
                <div class="col-md-4">
                    <x-forms.label fieldId="date" :fieldLabel="__('app.date')" />
                    <p class="form-control-static">{{ \Carbon\Carbon::parse($voucher->date)->format(company()->date_format) }}</p>
                </div>

                <!-- Payment Method -->
                <div class="col-md-4">
                    <x-forms.label fieldId="payment_method" :fieldLabel="__('modules.payments.paymentMethod')" />
                    <p class="form-control-static">{{ ucfirst($voucher->payment_method)?? '--' }}</p>
                </div>

                <!-- Reference Numbers -->
                <div class="col-md-4">
                    <x-forms.label fieldId="check_number" :fieldLabel="__('modules.vouchers.checkNumber')" />
                    <p class="form-control-static">{{ $voucher->check_number ?? '--' }}</p>
                </div>

                <div class="col-md-4">
                    <x-forms.label fieldId="bank_reference" :fieldLabel="__('modules.vouchers.bankReference')" />
                    <p class="form-control-static">{{ $voucher->bank_reference ?? '--' }}</p>
                </div>

                <div class="col-md-4">
                    <x-forms.label fieldId="deposit_slip" :fieldLabel="__('modules.vouchers.depositSlip')" /> @if($voucher->deposit_slip)
                            <a href="javascript:;" class="preview-deposit-slip" data-toggle="tooltip" data-original-title="@lang('app.preview')">
                                <i class="fa fa-eye text-info"></i>
                            </a>
                    <div class="d-flex align-items-center">
                        
                        <p class="form-control-static mb-0 mr-2">{{ $voucher->deposit_slip ?? '--' }}
                        @endif
                        </p>
                        
                    </div>
                </div>

                <div class="col-md-4">
                    <x-forms.label fieldId="cashier_info" :fieldLabel="__('modules.vouchers.cashierInfo')" />
                    <p class="form-control-static">{{ $voucher->cashier_info ?? '--' }}</p>
                </div>

                <!-- Memo -->
                <div class="col-md-12">
                    <x-forms.label fieldId="memo" :fieldLabel="__('app.description')" />
                    <p class="form-control-static">{{ $voucher->memo ?? '--' }}</p>
                </div>
            </div>

            <!-- Journal Entry Lines -->
            <h5 class="p-20 border-top-grey">@lang('modules.vouchers.entryLines')</h5>
            <div class="row px-20 pb-20">
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>@lang('modules.accounts.account')</th>
                                <th>@lang('modules.vouchers.debit')</th>
                                <th>@lang('modules.vouchers.credit')</th>
                                <th>@lang('app.note')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($voucher->lines as $line)
                                <tr>
                                    <td>{{ $line->chartOfAccount->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($line->debit, 2) }}</td>
                                    <td>{{ number_format($line->credit, 2) }}</td>
                                    <td>{{ $line->memo ?? '--' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-right">@lang('app.total')</th>
                                <th>{{ number_format($voucher->lines->sum('debit'), 2) }}</th>
                                <th>{{ number_format($voucher->lines->sum('credit'), 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deposit Slip Preview Modal -->
<div class="modal fade" id="depositSlipModal" tabindex="-1" role="dialog" aria-labelledby="depositSlipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depositSlipModalLabel">@lang('modules.vouchers.depositSlipPreview')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="deposit-slip-image" src="" class="img-fluid" alt="Deposit Slip">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('app.close')</button>
                <a href="" id="download-deposit-slip" class="btn btn-primary" download>@lang('app.download')</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.preview-deposit-slip').click(function() {
        // Get the deposit slip filename
        const depositSlip = @json($voucher->deposit_slip);

        // Set image source path using the JournalEntry::FILE_PATH
        const imagePath = "{{ asset('user-uploads/' . \App\Models\JournalEntry::FILE_PATH) }}/" + depositSlip;

        // Update modal image source and download link
        $('#deposit-slip-image').attr('src', imagePath);
        $('#download-deposit-slip').attr('href', imagePath);

        // Show the modal
        $('#depositSlipModal').modal('show');
    });
});
</script>

@endpush
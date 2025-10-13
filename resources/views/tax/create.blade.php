@php
    $manageTaxPermission = user()->permission('manage_tax');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.invoices.tax')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-table class="table-bordered" headType="thead-light">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('modules.invoices.taxName')</th>
            <th>@lang('modules.invoices.rate') %</th>
        </x-slot>

        @forelse($taxes as $key=>$tax)
            <tr id="cat-{{ $tax->id }}">
                <td>{{ $key + 1 }}</td>
                <td data-row-id="{{ $tax->id }}" data-row-type="tax_name" contenteditable="true">
                    {{ $tax->tax_name }}</td>
                <td data-row-id="{{ $tax->id }}" data-row-type="rate_percent" contenteditable="true">
                    {{ $tax->rate_percent }}</td>
            </tr>
        @empty
            <x-cards.no-record-found-list />
        @endforelse
    </x-table>

    <x-form id="createTax">
        <div class="row border-top-grey ">
            {{-- //income account --}}
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
            <div class="col-sm-4">
                <x-forms.text fieldId="tax_name" :fieldLabel="__('modules.invoices.taxName')" fieldName="tax_name" fieldRequired="true"
                    fieldPlaceholder="">
                </x-forms.text>
            </div>
            <div class="col-sm-4">
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
                    $('#tax_id').html(response.data);
                    $('#tax_id').selectpicker('refresh');
                    $(MODAL_LG).modal('hide');
                }
            }
        })
    });

    $('[contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
        let rowId = $(this).data('row-id');
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html();

            let type = $(this).data('row-type');

            var url = "{{ route('taxes.update', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#row-' + id,
                type: "POST",
                data: {
                    'value': value,
                    'type': type,
                    '_token': token,
                    '_method': 'PUT'
                },
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        $('#tax_id').html(response.data);
                        $('#tax_id').selectpicker('refresh');
                    }
                }
            })
        }
    });
</script>

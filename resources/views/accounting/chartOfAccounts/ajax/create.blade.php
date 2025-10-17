<style>
    .mt {
        margin-top: -4px;
    }
</style>

<div class="row">
    <div class="col-sm-12">

        <x-form id="save-coa-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('modules.accounts.createCoaTitle')
                </h4>

                <div class="row p-20">

                    {{-- Account Name --}}
                    <div class="col-md-6">
                        <x-forms.text 
                            fieldId="name" 
                            :fieldLabel="__('app.name')" 
                            fieldName="name"
                            fieldRequired="true" 
                            :fieldPlaceholder="__('app.name')">
                        </x-forms.text>
                    </div>

                    {{-- Account Code --}}
                    <div class="col-md-6">
                        <x-forms.text 
                            fieldId="code" 
                            :fieldLabel="__('app.code')" 
                            fieldName="code"
                            fieldRequired="true"
                            :fieldPlaceholder="__('app.code')">
                        </x-forms.text>
                    </div>

                    {{-- Account Type + Subtype --}}
                    <div class="col-md-6 mt-3">
                        <x-forms.label fieldId="account_sub_type_id" :fieldLabel="__('Account Type')" fieldRequired="true" />
                        <x-forms.input-group>
                            <select class="form-control select-picker mt" name="account_sub_type_id" id="account_sub_type_id" fieldRequired="true"
                                data-live-search="true">
                                <option value="">Select</option>

                                @foreach($accountTypes as $groupName => $types)
                                    <optgroup label="{{ $groupName }}">
                                        @foreach($types as $type)
                                            @foreach($type->subtypes as $subtype)
                                                <option value="{{ $subtype->id }}">
                                                    {{ $subtype->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    {{-- Parent COA (Optional) --}}
                    <div class="col-md-6 mt-3">
                        <x-forms.label fieldId="parent_id" :fieldLabel="__('Parent Account')" />
                        <x-forms.input-group>
                            <select class="form-control select-picker mt" name="parent_id" id="parent_id" data-live-search="true">
                                <option value="">-- None --</option>
                            </select>
                        </x-forms.input-group>
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12 mt-3">
                        <x-forms.textarea 
                            fieldId="description" 
                            :fieldLabel="__('app.description')" 
                            fieldName="description"
                            :fieldPlaceholder="__('app.description')">
                        </x-forms.textarea>
                    </div>

                </div>

                {{-- Actions --}}
                <x-form-actions>
                    <x-forms.button-primary id="save-coa-btn" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>

                    <x-forms.button-cancel :link="route('coa.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function () {
        $('.select-picker').selectpicker();
    });

    $('#save-coa-btn').click(function () {
        var url = "{{ route('coa.store') }}";

        $.easyAjax({
            url: url,
            container: '#save-coa-data-form',
            type: "POST",
            data: $('#save-coa-data-form').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-coa-btn",
            success: function (response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl;
                }
            }
        });
    });
    $('#account_sub_type_id').on('change', function () {
    let subtypeId = $(this).val();

    if (!subtypeId) {
        $('#parent_id').html('<option value="">-- None --</option>').selectpicker('refresh');
        return;
    }

    $.ajax({
        url: "{{ route('coa.getParents') }}",  // Create this route
        type: "GET",
        data: { sub_type_id: subtypeId },
        success: function (res) {
            let options = '<option value="">-- None --</option>';
            if (res.length > 0) {
                res.forEach(function (item) {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });
            }
            $('#parent_id').html(options).selectpicker('refresh');
        }
    });
});

</script>

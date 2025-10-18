<x-form id="editLabel" method="PUT" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title">@lang('modules.deal.editLabel')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <style>
        .form-control {
            padding: 6px 6px 6px;
        }
    </style>
    <div class="modal-body">
        <div class="form-body">
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <x-forms.select fieldId="lead_pipeline_id" :fieldLabel="__('modules.deal.pipeline')"
                        fieldName="lead_pipeline_id" fieldRequired="true">
                        @foreach($pipelines as $pipeline)
                            <option value="{{ $pipeline->id }}" {{ $pipeline->id == $label->pipeline_id ? 'selected' : '' }}>
                                {{ $pipeline->name }}
                            </option>
                        @endforeach
                    </x-forms.select>
                </div>

                <div class="col-lg-12 mb-3">
                    <x-forms.text fieldId="name" :fieldLabel="__('app.name')"
                        fieldName="name" :fieldValue="$label->name" fieldRequired="true" />
                </div>

                {{-- Bootstrap color select --}}
                <div class="col-lg-12 mb-3">
                    <x-forms.label fieldId="label_color" :fieldLabel="__('modules.deal.labelColor')" fieldName="label_color" />
                    <select name="label_color" id="label_color" class="form-control select-picker" data-live-search="true">
                        @php
                            $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
                        @endphp
                        @foreach($colors as $color)
                            <option value="{{ $color }}" class="text-{{ $color }} bg-light"
                                {{ $label->label_color === $color ? 'selected' : '' }}>
                                {{ ucfirst($color) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">
            @lang('app.close')
        </x-forms.button-cancel>
        <x-forms.button-primary id="update-label" icon="check">
            @lang('app.save')
        </x-forms.button-primary>
    </div>
</x-form>

<script>
$('#update-label').click(function() {
    $.easyAjax({
        url: "{{ route('pipeline-labels.update', $label->id) }}",
        container: '#editLabel',
        type: "POST",
        blockUI: true,
        disableButton: true,
        buttonSelector: "#update-label",
        data: $('#editLabel').serialize(),
        success: function(response) {
            if (response.status == "success") {
                window.location.reload();
            }
        }
    })
});
</script>

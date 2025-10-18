<x-form id="createLabelForm" method="POST" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title">@lang('modules.deal.addLabel')</h5>
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
                    <x-forms.select fieldId="lead_pipeline_id" :fieldLabel="__('modules.deal.pipeline')" fieldName="lead_pipeline_id"
                        fieldRequired="true">
                        @foreach ($pipelines as $pipeline)
                            <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
                <div class="col-lg-12">
                    <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" />
                </div>

                <div class="col-lg-12 mt-3">
                    <x-forms.label fieldId="label_color" :fieldLabel="__('modules.deal.labelColor')" fieldName="label_color" />
                    <select name="label_color" id="label_color" class="form-control select-picker"
                        data-live-search="true">
                        <option value="primary" class="text-primary bg-light">Primary</option>
                        <option value="secondary" class="text-secondary bg-light">Secondary</option>
                        <option value="success" class="text-success bg-light">Success</option>
                        <option value="danger" class="text-danger bg-light">Danger</option>
                        <option value="warning" class="text-warning bg-light">Warning</option>
                        <option value="info" class="text-info bg-light">Info</option>
                        <option value="light" class="text-dark bg-light">Light</option>
                        <option value="dark" class="text-light bg-dark">Dark</option>
                    </select>
                </div>

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
        <x-forms.button-primary id="save-label" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
    $('#save-label').click(function() {
        $.easyAjax({
            url: "{{ route('pipeline-labels.store') }}",
            container: '#createLabelForm',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: "#save-label",
            data: $('#createLabelForm').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });
</script>

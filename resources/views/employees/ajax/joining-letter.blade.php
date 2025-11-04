@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')

    @php

    @endphp

@endpush

@section('content')
    <div class="content-wrapper">
        {{-- <div class="d-flex justify-content-between action-bar">
            <h4>Joining Letter Template</h4>
        </div> --}}

        <div class="bg-white p-4 rounded shadow-sm">
            <form action="{{ route('templates.joining.store') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="title">Template Title</label>
                    <input type="text" name="title" id="title" class="form-control p-2" value="{{ $template->title ?? '' }}"
                        placeholder="Enter template title">
                </div>

                <div class="form-group mb-3">
                    <label>Available Tags</label>
                    <div class="border rounded p-3" style="background:#f8f9fa;">
                        @foreach($tags as $tag)
                            <button type="button" class="btn btn-sm btn-secondary m-1"
                                onclick="insertTag('{{ $tag }}')">
                                {{ $tag }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="content">Template Content</label>
                    <textarea name="content" id="template-editor" rows="10" class="form-control">
                                {{ $template->content ?? '' }}
                            </textarea>
                </div>

                <textarea name="contenthtml" class="d-none" id="contenthtml">
                            %3Cp%3E%3Cstrong%3EBold%20Text%26nbsp%3B%20through%20wysiwyg%20opts%20asfaf%26nbsp%3B%7Buser_rtl%7D%26nbsp%3B%7Buser_company_id%7D%26nbsp%3B%7Buser_gender%7D%26nbsp%3B%7Buser_name%7D%26nbsp%3B%7Buser_email%7D%26nbsp%3B%7Buser_location_details%7D%3C%2Fstrong%3E%3C%2Fp%3E%0A
                        </textarea>

                <button type="submit" id="lettersubmit" class="btn btn-primary">Save Template</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/4.21.0/full/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('template-editor', {
            height: 400
        });

        function insertTag(tag) {
            CKEDITOR.instances['template-editor'].insertText(tag);
        }

        document.getElementById('lettersubmit').addEventListener('click', function (e) {
            e.preventDefault();

            // Sync CKEditor data back to textarea
            CKEDITOR.instances['template-editor'].updateElement();

            // Get HTML content
            let html = CKEDITOR.instances['template-editor'].getData();

            // Encode and place it in hidden field
            document.getElementById('contenthtml').value = encodeURIComponent(html);

            // Now submit form
            this.closest('form').submit();
        });
    </script>

@endpush
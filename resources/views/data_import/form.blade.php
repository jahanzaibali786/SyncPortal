@extends('layouts.app')
@push('datatable-styles')
    @include('sections.datatable_css')
@endpush
@section('page-title')
    {{ __('Manage Imports') }}
@endsection
@section('content')
    <div class="container mx-auto card mt-4 py-2">
        <h3 class="">{{ __('Import Data via Excel') }}</h3>
        <form action="{{ route('data.import') }}" method="POST" enctype="multipart/form-data" style="width:100%;">
            @csrf
            <div class="mb-4 w-full">
                <label for="data_type" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Data Type') }}</label>
                <select style="width:100%;" name="data_type" id="data_type" required
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="" selected disabled>{{ __('Select Data Type') }}</option>
                    <option value="leads">{{ __('Leads') }}</option>
                </select>
            </div>
            <div class="mb-6 w-full">
                <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Excel File') }}</label>
                <input style="width:100%;" type="file" id="excel_file" name="excel_file" accept=".xlsx,.csv,.xls"
                    required
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('Accepted formats: .csv, .txt') }}</p>
            </div>
            <button type="submit"
                class="bg-primary text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                {{ __('Import Data') }}
            </button>
        </form>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('form').addEventListener('submit', function(e) {
                    console.log('form submitted');
                    let counter = {{ Session::get('counter', 0) }};
                    let counterBox = document.getElementById('counter-box');
                    let counterValue = document.getElementById('counter-value');
                    counterBox.classList.remove('hidden');
                    counterValue.textContent = counter;
                    let interval = setInterval(function() {
                        counter++;
                        counterValue.textContent = counter;
                    }, 1000);
                });
            });
        </script>
    </div>
@endsection

<x-form id="save-transfer" method="POST" class="ajax-form" :action="route('transfers.store')">
    @csrf
    @include('employees.transfers._form', [
        'transfer'    => null,
        'employees'   => $employees,
        'departments' => $departments,
    ])
</x-form>

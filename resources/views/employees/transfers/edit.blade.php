<x-form id="update-transfer" method="POST" class="ajax-form" :action="route('transfers.update', $transfer)">
    @csrf
    @method('PUT')
    @include('employees.transfers._form', [
        'transfer'    => $transfer,
        'employees'   => $employees,
        'departments' => $departments,
    ])
</x-form>

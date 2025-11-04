<x-form id="update-trip" method="POST" class="ajax-form" :action="route('trips.update', $trip)">
    @csrf
    @method('PUT')
    @include('trips._form', ['trip' => $trip])
</x-form>

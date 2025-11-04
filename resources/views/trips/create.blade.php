<x-form id="save-trip" method="POST" class="ajax-form" :action="route('trips.store')">
    @csrf
    @include('trips._form', ['trip' => null])
</x-form>

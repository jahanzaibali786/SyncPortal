<x-form id="save-complaint" method="POST" class="ajax-form" :action="route('complaints.store')">
    @csrf
    @include('complaints._form', ['complaint' => null, 'users' => $users])
</x-form>

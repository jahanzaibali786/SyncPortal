<x-form id="update-complaint" method="POST" class="ajax-form" :action="route('complaints.update', $complaint)">
    @csrf
    @method('PUT')
    @include('complaints._form', ['complaint' => $complaint, 'users' => $users])
</x-form>

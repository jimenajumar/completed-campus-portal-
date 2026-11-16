@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-blue-950">System Settings</h2>
    <p class="text-gray-500">Manage Campus Portal settings.</p>
</div>

<div class="bg-white rounded-xl shadow p-6 max-w-2xl">

    <h3 class="text-lg font-bold text-blue-950 mb-2">
        Club Membership Settings
    </h3>

    <p class="text-sm text-gray-500 mb-6">
        Set the maximum number of clubs a student can join.
    </p>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form id="settingsForm"
      action="{{ route('admin.settings.update') }}"
      method="POST">
        @csrf
        @method('PUT')

        <label for="max_clubs" class="block text-sm font-semibold text-gray-700 mb-2">
            Maximum Clubs per Student
        </label>

        <input
            type="number"
            name="max_clubs"
            id="max_clubs"
            value="{{ old('max_clubs', $maxClubs) }}"
            min="1"
            max="20"
            required
            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500"
        >

        <p class="text-xs text-gray-500 mt-2">
            Allowed range: 1–20 clubs.
        </p>

        <button
            type="submit"
            class="mt-5 bg-blue-950 hover:bg-blue-800 text-white px-6 py-3 rounded-lg font-semibold"
        >
            Save Settings
        </button>
    </form>

</div>


<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;

    Swal.fire({
        title: 'Save Settings?',
        text: 'Are you sure you want to update the maximum clubs per student?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#173F91',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, save settings',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            HTMLFormElement.prototype.submit.call(form);
        }
    });
});
</script>

@if(session('success'))
<script>
Swal.fire({
    title: 'Settings Updated!',
    text: @json(session('success')),
    icon: 'success',
    confirmButtonColor: '#173F91'
});
</script>
@endif


@endsection

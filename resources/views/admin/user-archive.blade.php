
@extends('layouts.admin')

@section('title', 'Archived Users - ACES')

@section('content')

<div class="max-w-7xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-blue-950">
            Archived Users
        </h1>

        <p class="text-gray-500 mt-2">
            View and manage previously removed user accounts.
        </p>
    </div>

    <!-- Navigation -->
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ url('/admin/users') }}"
           class="bg-red-800 text-white px-5 py-3 rounded-lg font-semibold hover:bg-red-900">
            Active Users
        </a>

        <span class="bg-gray-200 text-gray-800 px-5 py-3 rounded-lg font-semibold">
            Archived Users
        </span>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Success!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#991B1B'
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Unable to Complete Action',
                    text: @json(session('error')),
                    icon: 'error',
                    confirmButtonColor: '#991B1B'
                });
            });
        </script>
    @endif

    <!-- Statistics -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <p class="text-gray-500 text-sm">Total Archived Users</p>

        <h2 class="text-3xl font-bold text-red-800">
            {{ $archivedUsers->count() }}
        </h2>
    </div>

    <!-- Archived Users Table -->
    <div class="bg-white rounded-xl shadow-md p-6">

        <div class="mb-6">
            <input
                type="text"
                id="archiveSearch"
                placeholder="Search by name or email..."
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-700 outline-none">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">

                <thead class="bg-red-800 text-white">
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Name</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Student ID</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Date Archived</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody id="archiveTable">

                    @forelse($archivedUsers as $user)

                        <tr class="border-b hover:bg-gray-50">

                            <td class="p-4">{{ $user->id }}</td>

                            <td class="p-4">
                                {{ $user->name }}
                            </td>

                            <td class="p-4">
                                {{ $user->email }}
                            </td>

                            <td class="p-4">
                                {{ $user->student_id ?? 'N/A' }}
                            </td>

                            <td class="p-4">
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>

                            <td class="p-4">
                                {{ $user->deleted_at?->format('M d, Y h:i A') ?? 'N/A' }}
                            </td>

                            <td class="p-4">
                                <div class="flex flex-wrap justify-center gap-2">

                                    <!-- Restore -->
                                    <form
                                        action="{{ route('admin.users.restore', $user->id) }}"
                                        method="POST"
                                        class="restoreUserForm">

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="bg-green-100 text-green-700 hover:bg-green-700 hover:text-white px-3 py-2 rounded-lg text-sm font-semibold transition">
                                            Restore
                                        </button>
                                    </form>

                                    <!-- Permanent Delete -->
                                    <form
                                        action="{{ route('admin.users.forceDelete', $user->id) }}"
                                        method="POST"
                                        class="permanentDeleteForm">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="bg-red-100 text-red-700 hover:bg-red-700 hover:text-white px-3 py-2 rounded-lg text-sm font-semibold transition">
                                            Delete Permanently
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fas fa-archive text-3xl text-gray-300"></i>
                                    <p>No archived users found.</p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <p id="noArchiveResults" class="hidden text-center text-gray-500 py-6">
            No matching archived users found.
        </p>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // SEARCH ARCHIVED USERS
    const searchInput = document.getElementById('archiveSearch');
    const rows = document.querySelectorAll('#archiveTable tr');
    const noResults = document.getElementById('noArchiveResults');

    searchInput.addEventListener('input', function () {

        const search = searchInput.value.toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {

            if (row.cells.length < 7) return;

            const name = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();

            const matches =
                name.includes(search) || email.includes(search);

            row.style.display = matches ? '' : 'none';

            if (matches) visibleCount++;
        });

        noResults.classList.toggle('hidden', visibleCount > 0);
    });


    // RESTORE CONFIRMATION
    document.querySelectorAll('.restoreUserForm').forEach(form => {

        form.addEventListener('submit', async function (event) {

            event.preventDefault();

            const confirmation = await Swal.fire({
                title: 'Restore User Account?',
                text: 'This account will become active again.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#15803D',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Restore',
                cancelButtonText: 'Cancel'
            });

            if (confirmation.isConfirmed) {
                form.submit();
            }
        });
    });


    // PERMANENT DELETE CONFIRMATION
    document.querySelectorAll('.permanentDeleteForm').forEach(form => {

        form.addEventListener('submit', async function (event) {

            event.preventDefault();

            const confirmation = await Swal.fire({
                title: 'Permanently Delete Account?',
                text: 'This action cannot be undone. The account will be permanently removed from the database.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Delete Permanently',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });

            if (confirmation.isConfirmed) {
                form.submit();
            }
        });
    });

});
</script>

@endsection

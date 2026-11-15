
@extends('layouts.admin')

@section('title', 'User Management - ACES')

@section('content')

<div class="max-w-7xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-blue-950">User Management</h1>
        <p class="text-gray-500 mt-2">
            Manage and view registered students, club advisors, and administrators.
        </p>
    </div>

<div class="mb-6 flex justify-end">
    <a href="{{ route('admin.users.archive') }}"
       class="inline-flex items-center gap-2 bg-gray-800 text-white px-5 py-3 rounded-lg font-semibold hover:bg-gray-900 transition">
        <i class="fas fa-archive"></i>
        View Archived Users
    </a>
</div>


    <!-- Success and Error Alerts -->
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
                    title: 'Unable to Remove Account',
                    text: @json(session('error')),
                    icon: 'error',
                    confirmButtonColor: '#991B1B'
                });
            });
        </script>
    @endif

    <!-- Add New User -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">

        <h2 class="text-xl font-bold text-blue-950 mb-4">
            Add New User
        </h2>

        @if($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded-lg mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="createUserForm"
              action="{{ url('/admin/users') }}"
              method="POST"
              class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @csrf

            <input
                type="text"
                name="name"
                placeholder="Full Name"
                value="{{ old('name') }}"
                required
                class="border rounded-lg p-3">

            <input
                type="email"
                name="email"
                placeholder="Email Address"
                value="{{ old('email') }}"
                required
                class="border rounded-lg p-3">

            <input
                type="password"
                name="password"
                placeholder="Password (minimum 8 characters)"
                required
                minlength="8"
                class="border rounded-lg p-3">

            <select name="role" required class="border rounded-lg p-3">
                <option value="">Select User Role</option>
                <option value="admin">Admin</option>
                <option value="club_advisor">Club Advisor</option>
                <option value="student">Student</option>
            </select>

            <input
                type="text"
                name="student_id"
                placeholder="Student ID (optional)"
                value="{{ old('student_id') }}"
                class="border rounded-lg p-3">

            <button
                type="submit"
                class="bg-red-800 text-white rounded-lg p-3 font-semibold hover:bg-red-900">
                Create User Account
            </button>

        </form>
    </div>

    <!-- User Statistics -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <p class="text-gray-500 text-sm">Total Registered Users</p>

        <h2 class="text-3xl font-bold text-red-800">
            {{ $users->count() }}
        </h2>
    </div>

    <!-- User Table -->
    <div class="bg-white rounded-xl shadow-md p-6">

        <!-- Search and Filter -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">

            <input
                type="text"
                id="userSearch"
                placeholder="Search by name or email..."
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-700 outline-none">

            <select
                id="roleFilter"
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-700 outline-none">

                <option value="all">All Roles</option>
                <option value="admin">Admin</option>
                <option value="club_advisor">Club Advisor</option>
                <option value="student">Student</option>

            </select>

        </div>

        <!-- Table -->
        <div class="overflow-x-auto">

            <table class="w-full text-left">

                <thead class="bg-red-800 text-white">
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Name</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Student ID</th>
                        <th class="p-4">Role</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody id="usersTable">

                    @forelse($users as $user)

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

                            <td class="p-4 text-center">

                                @if(auth()->id() !== $user->id)

                                    <form
                                        action="{{ url('/admin/users/' . $user->id) }}"
                                        method="POST"
                                        class="removeUserForm inline">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="bg-red-100 text-red-700 hover:bg-red-700 hover:text-white px-4 py-2 rounded-lg text-sm font-semibold transition">

                                            Remove

                                        </button>

                                    </form>

                                @else

                                    <span class="text-gray-400 text-sm">
                                        Current Account
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">
                                No registered users found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <p id="noResults" class="hidden text-center text-gray-500 py-6">
            No matching users found.
        </p>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // SEARCH AND ROLE FILTER
    const searchInput = document.getElementById('userSearch');
    const roleFilter = document.getElementById('roleFilter');
    const rows = document.querySelectorAll('#usersTable tr');
    const noResults = document.getElementById('noResults');

    function filterUsers() {

        const search = searchInput.value.toLowerCase();
        const role = roleFilter.value;

        let visibleCount = 0;

        rows.forEach(row => {

            const name = row.cells[1]?.textContent.toLowerCase() || '';
            const email = row.cells[2]?.textContent.toLowerCase() || '';

            const userRole = row.cells[4]?.textContent
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '_') || '';

            const matchesSearch =
                name.includes(search) || email.includes(search);

            const matchesRole =
                role === 'all' || userRole === role;

            if (matchesSearch && matchesRole && row.cells.length === 6) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }

        });

        noResults.classList.toggle('hidden', visibleCount > 0);
    }

    searchInput.addEventListener('input', filterUsers);
    roleFilter.addEventListener('change', filterUsers);


    // CREATE USER CONFIRMATION
    const createUserForm = document.getElementById('createUserForm');

    createUserForm.addEventListener('submit', async function (event) {

        event.preventDefault();

        const confirmation = await Swal.fire({
            title: 'Create User Account?',
            text: 'Are you sure you want to create this user?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#991B1B',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, Create User',
            cancelButtonText: 'Cancel'
        });

        if (confirmation.isConfirmed) {
            createUserForm.submit();
        }

    });


    // REMOVE USER CONFIRMATION
    const removeForms = document.querySelectorAll('.removeUserForm');

    removeForms.forEach(form => {

        form.addEventListener('submit', async function (event) {

            event.preventDefault();

            const confirmation = await Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to remove this account? This will archive the account and prevent the user from accessing it.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Remove Account',
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


@extends('layouts.app')

@section('title', 'Profile - ACES')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-10">

    <h1 class="text-3xl font-bold text-blue-950">My Profile</h1>
    <p class="text-gray-500 mt-2">View your student information.</p>

    <div class="bg-white rounded-xl shadow-md p-8 mt-8">

        <!-- Student Profile -->
        <div class="flex flex-col sm:flex-row items-center gap-6 mb-8">
            <div class="w-28 h-28 rounded-full bg-red-100 flex items-center justify-center text-5xl">
                👤
            </div>

            <div class="text-center sm:text-left">
                <h2 id="profileName" class="text-2xl font-bold text-blue-950">
                    Loading...
                </h2>
                <p class="text-gray-500">Student</p>
                <p class="text-gray-500">ACES Tagum College Inc.</p>
            </div>
        </div>

        <hr class="my-8">

        <!-- Student Information -->
        <h3 class="text-xl font-bold text-blue-950 mb-6">
            Student Information
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-500 text-sm">Student ID</p>
                <p id="profileStudentId" class="font-semibold">Loading...</p>
            </div>

            <div>
                <p class="text-gray-500 text-sm">Email Address</p>
                <p id="profileEmail" class="font-semibold">Loading...</p>
            </div>

            <div>
                <p class="text-gray-500 text-sm">Account Role</p>
                <p id="profileRole" class="font-semibold">Student</p>
            </div>
        </div>

        <p id="profileError" class="text-red-600 mt-6"></p>

        <hr class="my-8">

        <!-- Change Password -->
        <h3 class="text-xl font-bold text-blue-950 mb-2">
            Change Password
        </h3>

        <p class="text-gray-500 text-sm mb-6">
            Update your password to keep your account secure.
        </p>

        <form id="changePasswordForm" class="space-y-5">

            <div>
                <label for="current_password"
                    class="block text-sm font-medium text-gray-700 mb-2">
                    Current Password
                </label>

                <input
                    type="password"
                    id="current_password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-900 focus:ring-blue-900"
                    placeholder="Enter your current password">
            </div>

            <div>
                <label for="new_password"
                    class="block text-sm font-medium text-gray-700 mb-2">
                    New Password
                </label>

                <input
                    type="password"
                    id="new_password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-900 focus:ring-blue-900"
                    placeholder="Enter your new password">
                <p class="text-xs text-gray-500 mt-1">
                    Use at least 8 characters.
                </p>
            </div>

            <div>
                <label for="confirm_password"
                    class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-900 focus:ring-blue-900"
                    placeholder="Confirm your new password">
            </div>

            <p id="passwordMessage" class="text-sm"></p>

            <button
                type="submit"
                id="changePasswordButton"
                class="bg-[#102B59] hover:bg-blue-900 text-white font-semibold px-6 py-3 rounded-lg disabled:opacity-50">

                Change Password
            </button>

        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const token = sessionStorage.getItem('campus_token');

    // Load Student Information
    if (!token) {
        document.getElementById('profileError').textContent =
            'Please log in again to view your profile.';
    } else {
        fetch('/api/user', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Unable to load profile information.');
            }

            return response.json();
        })
        .then(user => {
            document.getElementById('profileName').textContent =
                user.name || 'No name provided';

            document.getElementById('profileStudentId').textContent =
                user.student_id || 'Not provided';

            document.getElementById('profileEmail').textContent =
                user.email || 'Not provided';

            document.getElementById('profileRole').textContent =
                user.role || 'Student';
        })
        .catch(error => {
            document.getElementById('profileError').textContent =
                error.message;
        });
    }

    // Change Password
    const passwordForm = document.getElementById('changePasswordForm');

    passwordForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const message = document.getElementById('passwordMessage');
        const button = document.getElementById('changePasswordButton');

        const currentPassword =
            document.getElementById('current_password').value;

        const newPassword =
            document.getElementById('new_password').value;

        const confirmPassword =
            document.getElementById('confirm_password').value;

        message.textContent = '';
        message.className = 'text-sm';

        if (!token) {
            message.textContent = 'Please log in again.';
            message.classList.add('text-red-600');
            return;
        }

        if (newPassword !== confirmPassword) {
            message.textContent = 'New passwords do not match.';
            message.classList.add('text-red-600');
            return;
        }

        if (currentPassword === newPassword) {
            message.textContent =
                'Your new password must be different from your current password.';
            message.classList.add('text-red-600');
            return;
        }

        button.disabled = true;
        button.textContent = 'Updating Password...';

        try {
            const response = await fetch('/api/change-password', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    password: newPassword,
                    password_confirmation: confirmPassword
                })
            });

            const result = await response.json();

            if (!response.ok) {
                if (result.errors) {
                    const firstError = Object.values(result.errors)[0];
                    throw new Error(firstError[0]);
                }

                throw new Error(
                    result.message || 'Unable to change password.'
                );
            }

            passwordForm.reset();

            message.textContent =
                result.message || 'Password changed successfully!';

            message.classList.add('text-green-600');

        } catch (error) {
            message.textContent = error.message;
            message.classList.add('text-red-600');

        } finally {
            button.disabled = false;
            button.textContent = 'Change Password';
        }
    });

});
</script>
@endsection

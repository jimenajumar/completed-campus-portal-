@extends('layouts.advisor')

@section('title', 'Club Announcements - ACES')

@section('page-heading', 'Club Announcements')

@section('content')

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-[#102B59]">
                Manage Club Announcements
            </h2>
            <p class="text-gray-500 mt-1">
                Create announcements for the clubs assigned to you.
            </p>
        </div>
    </div>

    <!-- Create Announcement -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <h3 class="text-lg font-bold text-[#102B59] mb-5">
            Create Announcement
        </h3>

        <form id="announcementForm" class="space-y-5">

            <input type="hidden" id="announcementId">

            <!-- Club Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Select Club
                </label>

                <select id="club_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
                    required>
                    <option value="">Loading your assigned clubs...</option>
                </select>

                <p class="text-xs text-gray-500 mt-1">
                    Choose which club will receive this announcement.
                </p>
            </div>

            <!-- Announcement Title -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Announcement Title
                </label>

                <input type="text"
                    id="title"
                    maxlength="255"
                    placeholder="Enter announcement title"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
                    required>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Category
                </label>

                <select id="category"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-900 focus:outline-none">
                    <option value="general">General</option>
                    <option value="meeting">Meeting</option>
                    <option value="activity">Activity</option>
                    <option value="reminder">Reminder</option>
                    <option value="important">Important</option>
                </select>
            </div>

            <!-- Content -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Announcement Content
                </label>

                <textarea id="content"
                    rows="5"
                    placeholder="Write your announcement here..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
                    required></textarea>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3">

                <button type="submit"
                    id="publishButton"
                    class="bg-[#102B59] hover:bg-[#173F91] text-white px-6 py-3 rounded-lg font-semibold transition">
                    Publish Announcement
                </button>

                <button type="button"
                    id="cancelEdit"
                    class="hidden bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold">
                    Cancel Edit
                </button>

            </div>

        </form>
    </div>

    <!-- Announcement List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
            <div>
                <h3 class="text-lg font-bold text-[#102B59]">
                    My Announcements
                </h3>
                <p class="text-sm text-gray-500">
                    Announcements for your assigned clubs.
                </p>
            </div>

            <button type="button"
                id="refreshAnnouncements"
                class="text-[#173F91] font-semibold hover:underline">
                Refresh List
            </button>
        </div>

        <div id="announcementList" class="space-y-4">
            <p class="text-gray-500 text-center py-8">
                Loading announcements...
            </p>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const token = sessionStorage.getItem('campus_token');

    const clubSelect = document.getElementById('club_id');
    const form = document.getElementById('announcementForm');
    const announcementList = document.getElementById('announcementList');
    const publishButton = document.getElementById('publishButton');
    const cancelEdit = document.getElementById('cancelEdit');

    let clubs = [];

    if (!token) {
        Swal.fire('Session expired', 'Please log in again.', 'warning');
        return;
    }

    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    };

    // Escape text before displaying API values in HTML.
    function escapeHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    // Load clubs assigned to the advisor.
    async function loadClubs() {
        try {
            const response = await fetch('/api/my-assigned-clubs', {
                headers: headers
            });

            if (!response.ok) {
                throw new Error('Unable to load assigned clubs.');
            }

            clubs = await response.json();

            clubSelect.innerHTML =
                '<option value="">Choose a club</option>';

            clubs.forEach(function (club) {
                clubSelect.innerHTML += `
                    <option value="${club.id}">
                        ${escapeHTML(club.name)}
                    </option>
                `;
            });

            if (clubs.length === 0) {
                clubSelect.innerHTML =
                    '<option value="">No assigned clubs available</option>';

                publishButton.disabled = true;
                publishButton.classList.add('opacity-50', 'cursor-not-allowed');
            }

        } catch (error) {
            clubSelect.innerHTML =
                '<option value="">Unable to load clubs</option>';

            Swal.fire('Error', error.message, 'error');
        }
    }

    // Load announcements.
    async function loadAnnouncements() {
        announcementList.innerHTML =
            '<p class="text-center text-gray-500 py-8">Loading announcements...</p>';

        try {
            const response = await fetch('/api/announcements', {
                headers: headers
            });

            if (!response.ok) {
                throw new Error('Unable to load announcements.');
            }

            const announcements = await response.json();

            if (announcements.length === 0) {
                announcementList.innerHTML = `
                    <div class="text-center py-10 text-gray-500">
                        <p class="font-semibold">No announcements yet.</p>
                        <p class="text-sm mt-1">Create your first club announcement above.</p>
                    </div>
                `;
                return;
            }

            announcementList.innerHTML = announcements.map(function (item) {

                const clubName = item.club
                    ? item.club.name
                    : 'General Announcement';

                return `
                    <div class="border border-gray-200 rounded-xl p-5 hover:shadow-sm transition">

                        <div class="flex flex-col md:flex-row md:justify-between gap-4">

                            <div class="flex-1">

                                <div class="flex flex-wrap items-center gap-2 mb-2">

                                    <span class="bg-blue-50 text-[#173F91] text-xs font-semibold px-3 py-1 rounded-full">
                                        ${escapeHTML(clubName)}
                                    </span>

                                    <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1 rounded-full">
                                        ${escapeHTML(item.category || 'General')}
                                    </span>

                                    <span class="text-xs font-semibold ${item.is_published ? 'text-green-700' : 'text-orange-600'}">
                                        ${item.is_published ? 'Published' : 'Draft'}
                                    </span>

                                </div>

                                <h4 class="text-lg font-bold text-[#102B59]">
                                    ${escapeHTML(item.title)}
                                </h4>

                                <p class="text-gray-600 text-sm mt-2 whitespace-pre-line">
                                    ${escapeHTML(item.content)}
                                </p>

                                <p class="text-xs text-gray-400 mt-3">
                                    ${item.created_at ? new Date(item.created_at).toLocaleString() : ''}
                                </p>

                            </div>

                            <div class="flex gap-2 items-start">

                                <button type="button"
                                    onclick="editAnnouncement(${Number(item.id)})"
                                    class="bg-blue-50 text-blue-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-100">
                                    Edit
                                </button>

                                <button type="button"
                                    onclick="deleteAnnouncement(${Number(item.id)})"
                                    class="bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-100">
                                    Delete
                                </button>

                            </div>

                        </div>

                    </div>
                `;

            }).join('');

        } catch (error) {
            announcementList.innerHTML =
                '<p class="text-center text-red-500 py-8">Failed to load announcements.</p>';

            Swal.fire('Error', error.message, 'error');
        }
    }

    // Create or update announcement.
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const id = document.getElementById('announcementId').value;

        const data = {
            club_id: Number(clubSelect.value),
            title: document.getElementById('title').value.trim(),
            content: document.getElementById('content').value.trim(),
            category: document.getElementById('category').value,
            is_published: true,
            published_at: new Date().toISOString()
        };

        if (!data.club_id) {
            Swal.fire('Select a club', 'Please choose a club first.', 'warning');
            return;
        }

        if (!data.title || !data.content) {
            Swal.fire('Required fields', 'Please complete the title and content.', 'warning');
            return;
        }

        publishButton.disabled = true;

        try {
            const response = await fetch(
                id ? `/api/announcements/${id}` : '/api/announcements',
                {
                    method: id ? 'PUT' : 'POST',
                    headers: headers,
                    body: JSON.stringify(data)
                }
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Unable to save announcement.'
                );
            }

            await Swal.fire(
                'Success!',
                result.message,
                'success'
            );

            resetForm();
            await loadAnnouncements();

        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        } finally {
            publishButton.disabled = false;
        }
    });

    // Edit announcement.
    window.editAnnouncement = async function (id) {

        try {
            const response = await fetch(`/api/announcements/${id}`, {
                headers: headers
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Unable to open announcement.');
            }

            const item = result;

            document.getElementById('announcementId').value = item.id;
            clubSelect.value = item.club_id;
            document.getElementById('title').value = item.title;
            document.getElementById('content').value = item.content;
            document.getElementById('category').value = item.category || 'general';

            publishButton.textContent = 'Update Announcement';
            cancelEdit.classList.remove('hidden');

            form.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    };

    // Delete announcement.
    window.deleteAnnouncement = async function (id) {

        const confirmation = await Swal.fire({
            title: 'Delete announcement?',
            text: 'This announcement will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B91C2B',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, delete it'
        });

        if (!confirmation.isConfirmed) return;

        try {
            const response = await fetch(`/api/announcements/${id}`, {
                method: 'DELETE',
                headers: headers
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Unable to delete announcement.');
            }

            await Swal.fire('Deleted!', result.message, 'success');

            await loadAnnouncements();

        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    };

    // Reset form.
    function resetForm() {
        form.reset();

        document.getElementById('announcementId').value = '';

        publishButton.textContent = 'Publish Announcement';
        cancelEdit.classList.add('hidden');
    }

    cancelEdit.addEventListener('click', resetForm);

    document.getElementById('refreshAnnouncements')
        .addEventListener('click', loadAnnouncements);

    // Initialize page.
    async function initializePage() {
        await loadClubs();
        await loadAnnouncements();
    }

    initializePage();

});
</script>

@endsection

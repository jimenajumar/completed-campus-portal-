@extends('layouts.advisor')

@section('title', 'Club Announcements - ACES')
@section('page-heading', 'Club Announcements')

@section('content')

<div class="space-y-6">

    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-[#102B59]">
            Manage Club Announcements
        </h2>
        <p class="text-gray-500 mt-1">
            Create announcements for the clubs assigned to you.
        </p>
    </div>

    <!-- Create Announcement -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <h3 class="text-lg font-bold text-[#102B59] mb-5">
            Create Announcement
        </h3>

        <form id="announcementForm" class="space-y-5">

            <input type="hidden" id="announcementId">

            <!-- Club -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Select Club
                </label>

                <select id="club_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    required>
                    <option value="">Loading your assigned clubs...</option>
                </select>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Announcement Title
                </label>

                <input type="text"
                    id="title"
                    maxlength="255"
                    placeholder="Enter announcement title"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    required>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Category
                </label>

                <select id="category"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3">
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
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    required></textarea>
            </div>

            <!-- Optional Room Reservation -->
            <div class="border border-gray-200 rounded-xl p-5 space-y-4">

                <label class="flex items-center gap-3 font-semibold text-[#102B59]">
                    <input type="checkbox"
                        id="reserveRoom"
                        class="w-4 h-4 text-[#173F91] rounded">

                    Reserve a Room (Optional)
                </label>

                <p class="text-sm text-gray-500">
                    You can publish without reserving a room. If you request
                    one, your announcement will remain unpublished until
                    Admin approves the reservation.
                </p>

                <div id="reservationFields" class="hidden space-y-4">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Select Room
                        </label>

                        <select id="room_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3">
                            <option value="">Loading rooms...</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Start Date and Time
                            </label>

                            <input type="datetime-local"
                                id="starts_at"
                                class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                End Date and Time
                            </label>

                            <input type="datetime-local"
                                id="ends_at"
                                class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>

                    </div>

                    <p class="text-xs text-orange-600">
                        Room reservations require Admin approval before publication.
                    </p>

                </div>
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

    const reserveRoom = document.getElementById('reserveRoom');
    const reservationFields = document.getElementById('reservationFields');
    const roomSelect = document.getElementById('room_id');
    const startsAt = document.getElementById('starts_at');
    const endsAt = document.getElementById('ends_at');

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

    // Escape API values before displaying them.
    function escapeHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    // Load assigned clubs.
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

            clubs.forEach(function(club) {
                clubSelect.innerHTML += `
                    <option value="${Number(club.id)}">
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

    // Load rooms.
    async function loadRooms() {
        try {
            const response = await fetch('/api/rooms', {
                headers: headers
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Unable to load rooms.');
            }

            const rooms = result.data || [];

            roomSelect.innerHTML =
                '<option value="">Choose a room</option>';

            rooms.forEach(function(room) {
                roomSelect.innerHTML += `
                    <option value="${Number(room.id)}">
                        ${escapeHTML(room.room_number)}
                        ${room.room_name ? '- ' + escapeHTML(room.room_name) : ''}
                        (${escapeHTML(room.room_type)})
                    </option>
                `;
            });

            if (rooms.length === 0) {
                roomSelect.innerHTML =
                    '<option value="">No rooms available</option>';
            }

        } catch (error) {
            roomSelect.innerHTML =
                '<option value="">Unable to load rooms</option>';

            Swal.fire('Error', error.message, 'error');
        }
    }

    // Toggle reservation fields.
    reserveRoom.addEventListener('change', function() {

        reservationFields.classList.toggle('hidden', !this.checked);

        roomSelect.required = this.checked;
        startsAt.required = this.checked;
        endsAt.required = this.checked;

    });

    // Load announcements.
    async function loadAnnouncements() {

        announcementList.innerHTML = `
            <p class="text-center text-gray-500 py-8">
                Loading announcements...
            </p>
        `;

        try {
            const response = await fetch('/api/announcements', {
                headers: headers
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Unable to load announcements.'
                );
            }

            // Supports either a direct array or Laravel's data wrapper.
            const announcements = Array.isArray(result)
                ? result
                : (result.data || []);

            if (announcements.length === 0) {
                announcementList.innerHTML = `
                    <div class="text-center py-10 text-gray-500">
                        <p class="font-semibold">No announcements yet.</p>
                        <p class="text-sm mt-1">
                            Create your first club announcement above.
                        </p>
                    </div>
                `;
                return;
            }

            announcementList.innerHTML = announcements.map(function(item) {

                const clubName = item.club
                    ? item.club.name
                    : 'General Announcement';

                const reservation = item.room_reservation || null;

                let statusHTML = '';

                if (reservation?.status === 'pending') {
                    statusHTML = `
                        <span class="text-xs font-semibold text-orange-600">
                            Pending Room Approval
                        </span>
                    `;
                } else if (reservation?.status === 'rejected') {
                    statusHTML = `
                        <span class="text-xs font-semibold text-red-600">
                            Reservation Rejected
                        </span>
                    `;
                } else if (reservation?.status === 'approved') {
                    statusHTML = `
                        <span class="text-xs font-semibold text-green-700">
                            Published - Room Approved
                        </span>
                    `;
                } else {
                    statusHTML = item.is_published
                        ? `<span class="text-xs font-semibold text-green-700">Published</span>`
                        : `<span class="text-xs font-semibold text-gray-600">Draft</span>`;
                }

                let roomHTML = '';

                if (reservation && reservation.room) {
                    roomHTML = `
                        <div class="mt-3 bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <p class="text-sm font-semibold text-[#102B59]">
                                Room: ${escapeHTML(reservation.room.room_number)}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${escapeHTML(reservation.room.room_name || '')}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${escapeHTML(reservation.status)}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${reservation.starts_at
                                    ? new Date(reservation.starts_at).toLocaleString()
                                    : ''}
                                -
                                ${reservation.ends_at
                                    ? new Date(reservation.ends_at).toLocaleString()
                                    : ''}
                            </p>
                            ${reservation.admin_note
                                ? `<p class="text-xs text-red-600 mt-2">Admin note: ${escapeHTML(reservation.admin_note)}</p>`
                                : ''}
                        </div>
                    `;
                }

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

                                    ${statusHTML}

                                </div>

                                <h4 class="text-lg font-bold text-[#102B59]">
                                    ${escapeHTML(item.title)}
                                </h4>

                                <p class="text-gray-600 text-sm mt-2 whitespace-pre-line">
                                    ${escapeHTML(item.content)}
                                </p>

                                ${roomHTML}

                                <p class="text-xs text-gray-400 mt-3">
                                    ${item.created_at
                                        ? new Date(item.created_at).toLocaleString()
                                        : ''}
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
            announcementList.innerHTML = `
                <p class="text-center text-red-500 py-8">
                    Failed to load announcements.
                </p>
            `;

            Swal.fire('Error', error.message, 'error');
        }
    }

    // Create or update announcement.
    form.addEventListener('submit', async function(event) {

        event.preventDefault();

        const id = document.getElementById('announcementId').value;
        const hasRoom = reserveRoom.checked;

        const data = {
            club_id: Number(clubSelect.value),
            title: document.getElementById('title').value.trim(),
            content: document.getElementById('content').value.trim(),
            category: document.getElementById('category').value
        };

        if (!data.club_id || !data.title || !data.content) {
            Swal.fire(
                'Required fields',
                'Please complete the club, title, and content.',
                'warning'
            );
            return;
        }

        if (id && hasRoom) {
            Swal.fire(
                'Editing unavailable',
                'Please turn off the room reservation option when editing an existing announcement.',
                'info'
            );
            return;
        }

        let endpoint = '/api/announcements';
        let method = 'POST';

        if (id) {
            endpoint = `/api/announcements/${id}`;
            method = 'PUT';
        } else if (hasRoom) {

            endpoint = '/api/room-reservations';

            data.room_id = Number(roomSelect.value);
            data.starts_at = startsAt.value;
            data.ends_at = endsAt.value;

            if (!data.room_id || !data.starts_at || !data.ends_at) {
                Swal.fire(
                    'Reservation details required',
                    'Please select a room and provide the start and end dates.',
                    'warning'
                );
                return;
            }

            if (new Date(data.ends_at) <= new Date(data.starts_at)) {
                Swal.fire(
                    'Invalid schedule',
                    'The end date and time must be later than the start date and time.',
                    'warning'
                );
                return;
            }
        }

        const confirmation = await Swal.fire({
            title: hasRoom && !id
                ? 'Submit room reservation?'
                : id ? 'Update announcement?' : 'Publish announcement?',

            text: hasRoom && !id
                ? 'Your announcement will remain unpublished until Admin approves the reservation.'
                : id
                    ? 'Save your announcement changes?'
                    : 'This announcement will be published immediately.',

            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#102B59',
            cancelButtonColor: '#6B7280',
            confirmButtonText: hasRoom && !id
                ? 'Submit Request'
                : id ? 'Update' : 'Publish'
        });

        if (!confirmation.isConfirmed) return;

        publishButton.disabled = true;

        try {
            const response = await fetch(endpoint, {
                method: method,
                headers: headers,
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {

                const validationMessage = result.errors
                    ? Object.values(result.errors).flat().join('\n')
                    : '';

                throw new Error(
                    validationMessage ||
                    result.message ||
                    'Unable to save announcement.'
                );
            }

            await Swal.fire(
                'Success!',
                result.message || 'Announcement saved successfully.',
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
    window.editAnnouncement = async function(id) {

        try {
            const response = await fetch(`/api/announcements/${id}`, {
                headers: headers
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Unable to open announcement.'
                );
            }

            const item = result.data || result;

            document.getElementById('announcementId').value = item.id;
            clubSelect.value = item.club_id;
            document.getElementById('title').value = item.title;
            document.getElementById('content').value = item.content;
            document.getElementById('category').value =
                item.category || 'general';

            reserveRoom.checked = false;
            reservationFields.classList.add('hidden');

            roomSelect.required = false;
            startsAt.required = false;
            endsAt.required = false;

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
    window.deleteAnnouncement = async function(id) {

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
                throw new Error(
                    result.message || 'Unable to delete announcement.'
                );
            }

            await Swal.fire(
                'Deleted!',
                result.message || 'Announcement deleted successfully.',
                'success'
            );

            await loadAnnouncements();

        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    };

    // Reset form.
    function resetForm() {

        form.reset();

        document.getElementById('announcementId').value = '';

        reserveRoom.checked = false;
        reservationFields.classList.add('hidden');

        roomSelect.required = false;
        startsAt.required = false;
        endsAt.required = false;

        publishButton.textContent = 'Publish Announcement';
        cancelEdit.classList.add('hidden');
    }

    cancelEdit.addEventListener('click', function() {
        resetForm();
    });

    document.getElementById('refreshAnnouncements')
        .addEventListener('click', loadAnnouncements);

    // Initialize.
    async function initializePage() {
        await loadClubs();
        await loadRooms();
        await loadAnnouncements();
    }

    initializePage();

});
</script>

@endsection

@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-[#102B59]">Room Reservation Requests</h1>
            <p class="text-gray-500 text-sm mt-1">
                Review room requests submitted by Club Advisors.
            </p>
        </div>

        <button onclick="loadReservations()"
            class="px-4 py-2 rounded-lg bg-[#173F91] text-white hover:bg-[#102B59]">
            Refresh
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">Pending</p>
            <h2 id="pendingCount" class="text-3xl font-bold text-yellow-600">0</h2>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">Approved</p>
            <h2 id="approvedCount" class="text-3xl font-bold text-green-600">0</h2>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">Rejected</p>
            <h2 id="rejectedCount" class="text-3xl font-bold text-red-600">0</h2>
        </div>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="p-5 border-b">
            <h2 class="font-bold text-[#102B59]">Reservation Requests</h2>
        </div>

        <div id="reservationList" class="p-5 space-y-4">
            <p class="text-center text-gray-500">Loading reservations...</p>
        </div>
    </div>

</div>

<script>
const token = sessionStorage.getItem('campus_token');

const headers = {
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
};

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

function formatDate(value) {
    if (!value) return 'Not specified';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return escapeHTML(value);

    return date.toLocaleString([], {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
}

function statusBadge(status) {
    const styles = {
        pending: 'bg-yellow-100 text-yellow-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700'
    };

    const style = styles[status] || 'bg-gray-100 text-gray-700';

    return `
        <span class="px-3 py-1 rounded-full text-xs font-semibold ${style}">
            ${escapeHTML(status || 'Unknown').toUpperCase()}
        </span>
    `;
}

async function loadReservations() {
    const list = document.getElementById('reservationList');

    list.innerHTML = `
        <p class="text-center text-gray-500">Loading reservations...</p>
    `;

    try {
        const response = await fetch('/api/room-reservations', {
            headers
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Unable to load reservations.');
        }

        const reservations = Array.isArray(result)
            ? result
            : (result.data || []);

        document.getElementById('pendingCount').textContent =
            reservations.filter(item => item.status === 'pending').length;

        document.getElementById('approvedCount').textContent =
            reservations.filter(item => item.status === 'approved').length;

        document.getElementById('rejectedCount').textContent =
            reservations.filter(item => item.status === 'rejected').length;

        if (reservations.length === 0) {
            list.innerHTML = `
                <div class="text-center py-10 text-gray-500">
                    No room reservation requests found.
                </div>
            `;
            return;
        }

        list.innerHTML = reservations.map(item => {
            const announcement = item.announcement || {};
            const room = item.room || {};
            const advisor = item.requested_by || {};
            const club = announcement.club || {};

            const isPending = item.status === 'pending';

            return `
                <div class="border rounded-xl p-5 space-y-4">

                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                        <div>
                            <h3 class="font-bold text-lg text-[#102B59]">
                                ${escapeHTML(announcement.title || 'Untitled Announcement')}
                            </h3>

                            <p class="text-sm text-gray-500">
                                ${escapeHTML(club.name || club.club_name || 'Unknown Club')}
                            </p>
                        </div>

                        ${statusBadge(item.status)}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                        <div>
                            <p class="text-gray-500">Requested By</p>
                            <p class="font-semibold">
                                ${escapeHTML(advisor.name || 'Unknown Advisor')}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">Room</p>
                            <p class="font-semibold">
                                ${escapeHTML(room.room_number || 'Unknown Room')}
                                ${room.room_name ? '- ' + escapeHTML(room.room_name) : ''}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">Start</p>
                            <p class="font-semibold">${formatDate(item.starts_at)}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">End</p>
                            <p class="font-semibold">${formatDate(item.ends_at)}</p>
                        </div>

                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm font-semibold text-[#102B59] mb-2">
                            Announcement Details
                        </p>

                        <p class="text-sm text-gray-700 whitespace-pre-line">
                            ${escapeHTML(announcement.content || 'No announcement content.')}
                        </p>
                    </div>

                    ${item.admin_note ? `
                        <div class="text-sm">
                            <p class="font-semibold text-gray-600">Admin Note</p>
                            <p class="text-gray-500">${escapeHTML(item.admin_note)}</p>
                        </div>
                    ` : ''}

                    ${isPending ? `
                        <div class="flex flex-wrap gap-3 pt-2">

                            <button
                                onclick="approveReservation(${Number(item.id)})"
                                class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
                                Approve
                            </button>

                            <button
                                onclick="rejectReservation(${Number(item.id)})"
                                class="px-5 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                                Reject
                            </button>

                        </div>
                    ` : ''}

                </div>
            `;
        }).join('');

    } catch (error) {
        list.innerHTML = `
            <p class="text-center text-red-600">${escapeHTML(error.message)}</p>
        `;
    }
}

async function approveReservation(id) {
    const confirmation = await Swal.fire({
        title: 'Approve Reservation?',
        text: 'The room will be reserved and the announcement will automatically be published.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, approve'
    });

    if (!confirmation.isConfirmed) return;

    try {
        const response = await fetch(`/api/room-reservations/${id}/approve`, {
            method: 'PATCH',
            headers,
            body: JSON.stringify({})
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Unable to approve reservation.');
        }

        await Swal.fire({
            title: 'Approved!',
            text: result.message || 'Reservation approved and announcement published.',
            icon: 'success'
        });

        loadReservations();

    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

async function rejectReservation(id) {
    const result = await Swal.fire({
        title: 'Reject Reservation?',
        text: 'Please provide a reason for rejecting this request.',
        input: 'textarea',
        inputPlaceholder: 'Enter rejection reason...',
        inputAttributes: {
            'aria-label': 'Rejection reason'
        },
        inputValidator: value => {
            if (!value || !value.trim()) {
                return 'A rejection reason is required.';
            }
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Reject Reservation'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(`/api/room-reservations/${id}/reject`, {
            method: 'PATCH',
            headers,
            body: JSON.stringify({
                admin_note: result.value.trim()
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Unable to reject reservation.');
        }

        await Swal.fire({
            title: 'Rejected',
            text: data.message || 'Reservation rejected successfully.',
            icon: 'success'
        });

        loadReservations();

    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

loadReservations();
</script>
@endsection


@extends('layouts.app')
@section('title', 'Announcements - ACES')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10">

    <h1 class="text-3xl font-bold text-blue-950">
        School Announcements
    </h1>

    <p class="text-gray-500 mt-2">
        Stay informed about important school updates.
    </p>

    <div class="bg-white rounded-xl shadow-md p-6 mt-8">
        <h2 class="text-xl font-bold text-blue-950 mb-6">
            Latest Announcements
        </h2>

        <div id="announcementList" class="space-y-4">
            <p class="text-gray-500 text-center">
                Loading announcements...
            </p>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const list = document.getElementById('announcementList');
    const token = sessionStorage.getItem('campus_token');

    if (!token) {
        list.textContent = 'Please log in to view announcements.';
        return;
    }

    function createDetail(label, value) {
        const wrapper = document.createElement('div');

        const heading = document.createElement('p');
        heading.className = 'text-xs text-gray-500';
        heading.textContent = label;

        const detail = document.createElement('p');
        detail.className = 'font-semibold text-gray-800';
        detail.textContent = value || 'Not specified';

        wrapper.append(heading, detail);

        return wrapper;
    }

    function formatDate(value) {
        if (!value) return 'Not specified';

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return 'Not specified';
        }

        return date.toLocaleString([], {
            dateStyle: 'medium',
            timeStyle: 'short'
        });
    }

    fetch('/api/announcements', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Unable to load announcements.');
        }

        return response.json();
    })
    .then(data => {

        list.innerHTML = '';

        const announcements = Array.isArray(data)
            ? data
            : (data.data || []);

        if (announcements.length === 0) {
            list.textContent = 'No announcements available.';
            return;
        }

        announcements.forEach(item => {

            const card = document.createElement('div');
            card.className = 'border border-gray-200 rounded-lg p-5';

            const title = document.createElement('h3');
            title.className = 'text-lg font-bold text-blue-950';
            title.textContent = item.title || 'Untitled Announcement';

            const content = document.createElement('p');
            content.className = 'text-gray-600 mt-3 whitespace-pre-line';
            content.textContent = item.content || item.message || '';

            const club = document.createElement('p');
            club.className = 'text-sm text-blue-800 font-semibold mt-2';
            club.textContent = item.club?.name
                ? 'Club: ' + item.club.name
                : '';

            const date = document.createElement('p');
            date.className = 'text-sm text-gray-400 mt-4';
            date.textContent = item.created_at
                ? 'Posted: ' + new Date(item.created_at).toLocaleDateString()
                : '';

            card.append(title, content);

            if (club.textContent) {
                card.appendChild(club);
            }

            // Display room details only after Admin approval.
            const reservation = item.room_reservation;

            if (
                reservation &&
                reservation.status === 'approved' &&
                reservation.room
            ) {

                const room = reservation.room;

                const roomSection = document.createElement('div');
                roomSection.className =
                    'mt-5 p-5 bg-blue-50 border border-blue-200 rounded-xl';

                const roomHeading = document.createElement('h4');
                roomHeading.className =
                    'font-bold text-blue-950 text-lg mb-4';

                roomHeading.textContent = 'Reserved Room Details';

                const details = document.createElement('div');
                details.className =
                    'grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm';

                details.append(
                    createDetail(
                        'Room Number',
                        room.room_number
                    ),
                    createDetail(
                        'Room Name',
                        room.room_name
                    ),
                    createDetail(
                        'Location',
                        room.location
                    ),
                    createDetail(
                        'Reservation Date',
                        formatDate(reservation.starts_at)
                    ),
                    createDetail(
                        'Start Time',
                        formatDate(reservation.starts_at)
                    ),
                    createDetail(
                        'End Time',
                        formatDate(reservation.ends_at)
                    )
                );

                const status = document.createElement('p');
                status.className =
                    'mt-4 text-sm font-semibold text-green-700';

                status.textContent = 'Room Reservation Approved';

                roomSection.append(roomHeading, details, status);
                card.appendChild(roomSection);
            }

            card.appendChild(date);
            list.appendChild(card);
        });
    })
    .catch(error => {
        list.textContent = error.message;
    });

});
</script>
@endsection

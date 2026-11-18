@extends('layouts.admin')

@section('content')
<div class="p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-blue-950">Archived Clubs</h2>
            <p class="text-gray-500">View and restore previously archived clubs.</p>
        </div>

        <a href="/admin/clubs"
           class="inline-flex items-center gap-2 bg-blue-950 hover:bg-blue-900 text-white px-5 py-3 rounded-lg font-semibold transition">
            <i class="fas fa-arrow-left"></i>
            Back to Clubs
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div id="archiveLoading" class="text-center py-10 text-gray-500">
            <i class="fas fa-spinner fa-spin text-2xl"></i>
            <p class="mt-2">Loading archived clubs...</p>
        </div>

        <div id="archiveEmpty" class="hidden text-center py-12">
            <i class="fas fa-archive text-5xl text-gray-300"></i>
            <h3 class="mt-4 text-lg font-semibold text-gray-700">No Archived Clubs</h3>
            <p class="text-gray-500 text-sm mt-1">Archived clubs will appear here.</p>
        </div>

        <div id="archiveList" class="hidden grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const token = sessionStorage.getItem('campus_token');

    const archiveLoading = document.getElementById('archiveLoading');
    const archiveEmpty = document.getElementById('archiveEmpty');
    const archiveList = document.getElementById('archiveList');

    async function apiRequest(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                ...options.headers
            }
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Something went wrong.');
        }

        return result;
    }

    function showError(error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Something went wrong.'
        });
    }

    async function loadArchivedClubs() {
        archiveLoading.classList.remove('hidden');
        archiveEmpty.classList.add('hidden');
        archiveList.classList.add('hidden');
        archiveList.innerHTML = '';

        try {
            const clubs = await apiRequest('/api/clubs-archive');

            archiveLoading.classList.add('hidden');

            if (!Array.isArray(clubs) || clubs.length === 0) {
                archiveEmpty.classList.remove('hidden');
                return;
            }

            archiveList.classList.remove('hidden');

            clubs.forEach(club => {
                const card = document.createElement('div');

                card.className = 'border border-gray-200 rounded-xl p-5 bg-gray-50';

                const name = document.createElement('h3');
                name.className = 'text-lg font-bold text-blue-950 mb-2';
                name.textContent = club.name || 'Unnamed Club';

                const description = document.createElement('p');
                description.className = 'text-sm text-gray-600 mb-3';
                description.textContent = club.description || 'No description available.';

                const archivedDate = document.createElement('p');
                archivedDate.className = 'text-xs text-gray-500 mb-4';

                const date = club.deleted_at
                    ? new Date(club.deleted_at).toLocaleString()
                    : 'Date unavailable';

                archivedDate.textContent = 'Archived: ' + date;

                const restoreButton = document.createElement('button');
                restoreButton.type = 'button';
                restoreButton.className =
                    'w-full bg-green-700 hover:bg-green-800 text-white px-4 py-2.5 rounded-lg font-semibold transition';
                restoreButton.innerHTML = '<i class="fas fa-undo mr-2"></i> Restore Club';

                restoreButton.addEventListener('click', () => restoreClub(club));

                card.append(name, description, archivedDate, restoreButton);
                archiveList.appendChild(card);
            });

        } catch (error) {
            archiveLoading.classList.add('hidden');
            showError(error);
        }
    }

    async function restoreClub(club) {
        const confirmation = await Swal.fire({
            title: 'Restore Club?',
            text: `Are you sure you want to restore "${club.name}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15803d',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, restore club',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        });

        if (!confirmation.isConfirmed) return;

        try {
            const result = await apiRequest(
                `/api/clubs/${club.id}/restore`,
                { method: 'PATCH' }
            );

            await Swal.fire({
                icon: 'success',
                title: 'Club Restored!',
                text: result.message || 'The club has been restored successfully.',
                confirmButtonColor: '#173F91'
            });

            await loadArchivedClubs();

        } catch (error) {
            showError(error);
        }
    }

    if (!token) {
        archiveLoading.classList.add('hidden');
        showError(new Error('Your session token was not found. Please log in again.'));
        return;
    }

    loadArchivedClubs();
});
</script>
@endsection

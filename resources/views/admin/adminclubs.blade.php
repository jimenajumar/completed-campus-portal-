@extends('layouts.admin')

@section('title', 'Clubs Management')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-blue-950">Clubs Management</h2>
        <p class="text-gray-500">Manage campus clubs and approval requests.</p>
    </div>

    <a href="{{ route('admin.clubs.archive') }}"
       class="inline-flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-900 text-white px-5 py-3 rounded-lg font-semibold transition">
        <i class="fas fa-archive"></i>
        Archived Clubs
    </a>
</div>

<div class="bg-white rounded-xl shadow p-6">

    <!-- ADD CLUB -->
    <div class="mb-6 border rounded-xl p-5">
        <h3 class="text-lg font-bold text-blue-950 mb-4">Add New Club</h3>

        <form id="addClubForm" class="space-y-3">
            <input
                type="text"
                id="clubName"
                placeholder="Club Name"
                class="w-full border rounded-lg px-4 py-2"
                required
            >

            <textarea
                id="clubDescription"
                placeholder="Club Description"
                class="w-full border rounded-lg px-4 py-2"
                rows="3"
            ></textarea>

            <button
                type="submit"
                class="bg-blue-950 hover:bg-blue-800 text-white px-5 py-2 rounded-lg">
                Add Club
            </button>
        </form>
    </div>

    <!-- SEARCH AND FILTER -->
    <div class="flex flex-col md:flex-row gap-3 mb-6">
        <input
            type="text"
            id="searchClub"
            placeholder="Search clubs..."
            class="border rounded-lg px-4 py-2 flex-1"
        >

        <select id="statusFilter" class="border rounded-lg px-4 py-2">
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    <div id="message" class="mb-4 text-sm"></div>

    <div id="clubList" class="space-y-4">
        <p class="text-gray-500">Loading clubs...</p>
    </div>

</div>

<!-- ADVISOR ASSIGNMENT HISTORY MODAL -->
<div id="advisorHistoryModal"
     class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 max-h-[85vh] overflow-y-auto">

        <div class="flex justify-between items-center mb-5">
            <h2 id="historyClubName" class="text-xl font-bold text-blue-950">
                Instructor Assignment History
            </h2>

            <button
                type="button"
                onclick="closeAdvisorHistory()"
                class="text-gray-500 hover:text-red-600 text-2xl">
                &times;
            </button>
        </div>

        <div id="advisorHistoryContent">
            <p class="text-gray-500">Loading history...</p>
        </div>

        <div class="flex justify-end mt-6">
            <button
                type="button"
                onclick="closeAdvisorHistory()"
                class="bg-blue-950 text-white px-5 py-2 rounded-lg">
                Close
            </button>
        </div>

    </div>
</div>

<script>
const token = sessionStorage.getItem('campus_token');
const clubList = document.getElementById('clubList');
const message = document.getElementById('message');

let clubs = [];
let advisors = [];

// ========================================
// SWEETALERT HELPERS
// ========================================

function showSuccess(title, text) {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        confirmButtonColor: '#102B59'
    });
}

function showError(error) {
    return Swal.fire({
        icon: 'error',
        title: 'Something Went Wrong',
        text: error.message || 'Please try again.',
        confirmButtonColor: '#102B59'
    });
}

function showMessage(text, success = false) {
    message.textContent = text;
    message.className = success
        ? 'mb-4 text-sm text-green-700'
        : 'mb-4 text-sm text-red-700';
}

// ========================================
// API REQUEST
// ========================================

async function apiRequest(url, options = {}) {

    if (!token) {
        throw new Error('Login token not found. Please log in again.');
    }

    const response = await fetch(url, {
        ...options,
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
            ...options.headers
        }
    });

    const result = await response.json().catch(() => ({}));

    if (!response.ok) {
        let errorMessage = result.message || 'Something went wrong.';

        if (result.errors) {
            errorMessage = Object.values(result.errors).flat().join('\n');
        }

        throw new Error(errorMessage);
    }

    return result;
}

// ========================================
// LOAD CLUBS
// ========================================

async function loadClubs() {
    try {
        clubs = await apiRequest('/api/clubs');
        renderClubs();

    } catch (error) {
        showMessage(error.message);
        clubList.replaceChildren();
    }
}

// ========================================
// RENDER CLUBS
// ========================================

function renderClubs() {

    const search = document.getElementById('searchClub')
        .value.toLowerCase();

    const status = document.getElementById('statusFilter').value;

    const filtered = clubs.filter(club => {

        const matchesSearch =
            (club.name || '').toLowerCase().includes(search) ||
            (club.description || '').toLowerCase().includes(search);

        return matchesSearch &&
            (status === 'all' || club.status === status);
    });

    clubList.replaceChildren();

    if (filtered.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'text-center text-gray-500 py-6';
        empty.textContent = 'No clubs found.';
        clubList.appendChild(empty);
        return;
    }

    filtered.forEach(club => {

        const card = document.createElement('div');
        card.className = 'border rounded-xl p-5';

        const name = document.createElement('h3');
        name.className = 'text-lg font-bold text-blue-950';
        name.textContent = club.name;

        const description = document.createElement('p');
        description.className = 'text-gray-600 my-3';
        description.textContent =
            club.description || 'No description provided.';

        const statusBadge = document.createElement('span');
        statusBadge.className =
            'inline-block px-3 py-1 rounded-full text-sm font-semibold';

        const colors = {
            pending: 'bg-yellow-100 text-yellow-700',
            approved: 'bg-green-100 text-green-700',
            rejected: 'bg-red-100 text-red-700'
        };

        const currentStatus = club.status || 'pending';

        statusBadge.className += ' ' +
            (colors[currentStatus] || 'bg-gray-100 text-gray-700');

        statusBadge.textContent = currentStatus;

        // ========================================
        // ADVISOR ASSIGNMENT
        // ========================================

        const advisorSection = document.createElement('div');
        advisorSection.className = 'mt-4 flex flex-wrap gap-2';

        const advisorSelect = document.createElement('select');
        advisorSelect.className = 'border rounded-lg px-3 py-2 flex-1';

        const unassigned = document.createElement('option');
        unassigned.value = '';
        unassigned.textContent = 'No advisor assigned';

        advisorSelect.appendChild(unassigned);

        advisors.forEach(advisor => {
            const option = document.createElement('option');

            option.value = advisor.id;
            option.textContent = advisor.name;

            option.selected =
                Number(club.advisor_id) === Number(advisor.id);

            advisorSelect.appendChild(option);
        });

        const assignButton = document.createElement('button');
        assignButton.textContent = 'Assign Advisor';
        assignButton.className =
            'bg-indigo-700 hover:bg-indigo-800 text-white px-4 py-2 rounded-lg';

        // ASSIGN OR REMOVE ADVISOR WITH SWEETALERT

        assignButton.addEventListener('click', async () => {

            const advisorId = advisorSelect.value;
            const advisorName =
                advisorSelect.selectedOptions[0]?.textContent;

            const confirmation = await Swal.fire({
                title: advisorId
                    ? 'Assign Club Advisor?'
                    : 'Remove Club Advisor?',
                text: advisorId
                    ? `Assign ${advisorName} to ${club.name}?`
                    : `Remove the current advisor from ${club.name}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#102B59',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, continue',
                cancelButtonText: 'Cancel'
            });

            if (!confirmation.isConfirmed) return;

            try {

                const result = await apiRequest(
                    `/api/clubs/${club.id}/assign-advisor`,
                    {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            advisor_id: advisorId || null
                        })
                    }
                );

                await showSuccess(
                    'Advisor Updated!',
                    result.message
                );

                await loadClubs();

            } catch (error) {
                showError(error);
            }
        });

        // ASSIGNMENT HISTORY

        const historyButton = document.createElement('button');
        historyButton.textContent = 'Assignment History';
        historyButton.className =
            'bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg';

        historyButton.addEventListener('click', () => {
            loadAdvisorHistory(club);
        });

        advisorSection.append(
            advisorSelect,
            assignButton,
            historyButton
        );

        // ========================================
        // CLUB ACTION BUTTONS
        // ========================================

        const actions = document.createElement('div');
        actions.className = 'flex flex-wrap gap-2 mt-4';

        const edit = document.createElement('button');
        edit.textContent = 'Edit';
        edit.className =
            'bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg';

        edit.addEventListener('click', () => editClub(club));

        const remove = document.createElement('button');
        remove.textContent = 'Archive';
        remove.className =
            'bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg';

        remove.addEventListener('click', () => deleteClub(club));

        actions.append(edit);

        // APPROVE AND REJECT

        if (currentStatus === 'pending') {

            const approve = document.createElement('button');
            approve.textContent = 'Approve';
            approve.className =
                'bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg';

            approve.addEventListener('click', () =>
                updateClubStatus(club.id, 'approve')
            );

            const reject = document.createElement('button');
            reject.textContent = 'Reject';
            reject.className =
                'bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg';

            reject.addEventListener('click', () =>
                updateClubStatus(club.id, 'reject')
            );

            actions.append(approve, reject);
        }

        actions.append(remove);

        card.append(
            name,
            description,
            statusBadge,
            advisorSection,
            actions
        );

        clubList.appendChild(card);
    });
}

// ========================================
// APPROVE OR REJECT CLUB
// ========================================

async function updateClubStatus(id, action) {

    const isApprove = action === 'approve';

    const confirmation = await Swal.fire({
        title: isApprove ? 'Approve Club?' : 'Reject Club?',
        text: isApprove
            ? 'Are you sure you want to approve this club?'
            : 'Are you sure you want to reject this club?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isApprove ? '#15803d' : '#dc2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: isApprove ? 'Yes, approve' : 'Yes, reject',
        cancelButtonText: 'Cancel'
    });

    if (!confirmation.isConfirmed) return;

    try {

        const result = await apiRequest(
            `/api/clubs/${id}/${action}`,
            {
                method: 'PATCH'
            }
        );

        await showSuccess(
            isApprove ? 'Club Approved!' : 'Club Rejected!',
            result.message
        );

        await loadClubs();

    } catch (error) {
        showError(error);
    }
}

// ARCHIVE CLUB
async function deleteClub(club) {

    const confirmation = await Swal.fire({
        title: 'Archive Club?',
        text: `Are you sure you want to archive "${club.name}"? You can restore it later from Archived Clubs.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, archive club',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });

    if (!confirmation.isConfirmed) return;

    try {

        const result = await apiRequest(
            `/api/clubs/${club.id}/archive`,
            {
                method: 'DELETE'
            }
        );

        await showSuccess('Club Archived!', result.message);

        await loadClubs();

    } catch (error) {
        showError(error);
    }
}

// ========================================
// EDIT CLUB
// ========================================

async function editClub(club) {

    const result = await Swal.fire({
        title: 'Edit Club',
        html: `
            <div class="text-left space-y-3">
                <label class="block text-sm font-semibold">Club Name</label>
                <input
                    id="swalClubName"
                    class="swal2-input"
                    placeholder="Club Name"
                    value="${escapeHtml(club.name || '')}"
                >

                <label class="block text-sm font-semibold">Description</label>
                <textarea
                    id="swalClubDescription"
                    class="swal2-textarea"
                    placeholder="Club Description"
                >${escapeHtml(club.description || '')}</textarea>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#102B59',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        preConfirm: () => {

            const name =
                document.getElementById('swalClubName').value.trim();

            const description =
                document.getElementById('swalClubDescription').value.trim();

            if (!name) {
                Swal.showValidationMessage('Club name is required.');
                return false;
            }

            return { name, description };
        }
    });

    if (!result.isConfirmed) return;

    const confirmation = await Swal.fire({
        title: 'Save Changes?',
        text: `Update the information for ${result.value.name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#102B59',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, update'
    });

    if (!confirmation.isConfirmed) return;

    try {

        const response = await apiRequest(
            `/api/clubs/${club.id}`,
            {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(result.value)
            }
        );

        await showSuccess('Club Updated!', response.message);

        await loadClubs();

    } catch (error) {
        showError(error);
    }
}

// Escape text before inserting it into the edit dialog.
function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);
}

// ========================================
// SEARCH AND FILTER
// ========================================

document.getElementById('searchClub')
    .addEventListener('input', renderClubs);

document.getElementById('statusFilter')
    .addEventListener('change', renderClubs);

// ========================================
// LOAD CLUB ADVISORS
// ========================================

async function loadAdvisors() {

    try {

        advisors = await apiRequest('/api/club-advisors');

        console.log('Loaded advisors:', advisors);

    } catch (error) {

        console.error('Advisor loading error:', error);
        showMessage(error.message);
    }
}

// ========================================
// ADD CLUB WITH SWEETALERT
// ========================================

document.getElementById('addClubForm')
    .addEventListener('submit', async function(e) {

        e.preventDefault();

        const name =
            document.getElementById('clubName').value.trim();

        const description =
            document.getElementById('clubDescription').value.trim();

        if (!name) {
            await Swal.fire({
                icon: 'warning',
                title: 'Club Name Required',
                text: 'Please enter a club name.',
                confirmButtonColor: '#102B59'
            });
            return;
        }

        const confirmation = await Swal.fire({
            title: 'Create New Club?',
            text: `Are you sure you want to create "${name}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#102B59',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, create club',
            cancelButtonText: 'Cancel'
        });

        if (!confirmation.isConfirmed) return;

        try {

            const result = await apiRequest('/api/clubs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    description: description
                })
            });

            this.reset();

            await showSuccess(
                'Club Created!',
                result.message || 'Club created successfully.'
            );

            await loadClubs();

        } catch (error) {
            showError(error);
        }
    });

// ========================================
// ADVISOR ASSIGNMENT HISTORY
// ========================================

function formatHistoryDate(value) {

    if (!value) return '—';

    const date = new Date(value);

    return Number.isNaN(date.getTime())
        ? value
        : date.toLocaleString();
}

async function loadAdvisorHistory(club) {

    const modal = document.getElementById('advisorHistoryModal');
    const content = document.getElementById('advisorHistoryContent');
    const title = document.getElementById('historyClubName');

    title.textContent =
        `${club.name} - Instructor Assignment History`;

    modal.classList.remove('hidden');

    content.innerHTML = `
        <p class="text-center text-gray-500 py-6">
            Loading assignment history...
        </p>
    `;

    try {

        const result = await apiRequest(
            `/api/clubs/${club.id}/advisor-history`
        );

        const history = result.history || [];

        content.replaceChildren();

        if (history.length === 0) {

            content.innerHTML = `
                <p class="text-center text-gray-500 py-6">
                    No instructor assignment history found.
                </p>
            `;

            return;
        }

        history.forEach(record => {

            const card = document.createElement('div');
            card.className = 'border rounded-lg p-4 mb-3';

            const header = document.createElement('div');
            header.className = 'flex flex-wrap justify-between gap-2';

            const name = document.createElement('h3');
            name.className = 'font-semibold text-[#102B59]';
            name.textContent =
                record.advisor_name || 'Unknown instructor';

            const badge = document.createElement('span');

            badge.className = record.status === 'Current'
                ? 'bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold'
                : 'bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold';

            badge.textContent = record.status;

            header.append(name, badge);

            const assigned = document.createElement('p');
            assigned.className = 'text-sm text-gray-600 mt-3';
            assigned.textContent =
                `Assigned: ${formatHistoryDate(record.assigned_at)}`;

            const assignedBy = document.createElement('p');
            assignedBy.className = 'text-sm text-gray-600';
            assignedBy.textContent =
                `Assigned by: ${record.assigned_by || 'Unknown'}`;

            const ended = document.createElement('p');
            ended.className = 'text-sm text-gray-600';

            ended.textContent = record.ended_at
                ? `Assignment ended: ${formatHistoryDate(record.ended_at)}`
                : 'Currently assigned';

            card.append(header, assigned, assignedBy, ended);

            content.appendChild(card);
        });

    } catch (error) {
        content.textContent = error.message;
    }
}

// ========================================
// CLOSE HISTORY MODAL
// ========================================

function closeAdvisorHistory() {
    document.getElementById('advisorHistoryModal')
        .classList.add('hidden');
}

// ========================================
// INITIALIZE PAGE
// ========================================

loadAdvisors().then(() => loadClubs());

</script>

@endsection

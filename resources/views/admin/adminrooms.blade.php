@extends('layouts.admin')

@section('title', 'Rooms Management')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-blue-950">Rooms Management</h2>
    <p class="text-gray-500">Manage campus rooms and facilities.</p>
</div>

<!-- ROOM FORM -->
<div class="bg-white rounded-xl shadow p-6 mb-6">
    <h3 class="text-lg font-bold mb-4" id="formTitle">Add New Room</h3>

    <form id="roomForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" id="roomId">

        <input id="roomNumber" required placeholder="Room Number"
            class="border rounded-lg px-4 py-3">

        <input id="roomName" placeholder="Room Name"
            class="border rounded-lg px-4 py-3">

        <input id="location" placeholder="Location"
            class="border rounded-lg px-4 py-3">

        <input id="capacity" type="number" min="0" required
            placeholder="Capacity" class="border rounded-lg px-4 py-3">

        <div>
            <label for="room_type" class="block text-sm font-medium text-gray-700">
                Room Type
            </label>

            <select
                id="room_type"
                name="room_type"
                required
                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">Select Room Type</option>
                <option value="Air Conditioning">Air Conditioning</option>
                <option value="Classroom">Classroom</option>
                <option value="Lecture Room">Lecture Room</option>
                <option value="Computer Laboratory">Computer Laboratory</option>
                <option value="Science Laboratory">Science Laboratory</option>
                <option value="Conference Room">Conference Room</option>
                <option value="Audio Visual Room">Audio Visual Room</option>
                <option value="Library">Library</option>
                <option value="Gymnasium">Gymnasium</option>
                <option value="Multipurpose Hall">Multipurpose Hall</option>
            </select>
        </div>

        <textarea id="description" placeholder="Description"
            class="border rounded-lg px-4 py-3 md:col-span-2"></textarea>

        <div class="flex gap-2 md:col-span-2">
            <button type="submit" id="saveButton"
                class="bg-red-800 hover:bg-red-900 text-white px-5 py-2 rounded-lg">
                Add Room
            </button>

            <button type="button" id="cancelButton"
                class="hidden bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg">
                Cancel
            </button>
        </div>
    </form>

    <p id="message" class="mt-3 text-sm"></p>
</div>

<!-- ROOM LIST -->
<div class="bg-white rounded-xl shadow p-6">

    <div class="flex flex-col md:flex-row gap-3 mb-6">
        <input id="searchRoom" placeholder="Search rooms..."
            class="border rounded-lg px-4 py-2 flex-1">

        <select id="typeFilter" class="border rounded-lg px-4 py-2">
            <option value="all">All Room Types</option>
        </select>
    </div>

    <div id="roomList" class="space-y-4">
        <p class="text-gray-500">Loading rooms...</p>
    </div>

</div>

<script>
const token = sessionStorage.getItem('campus_token');
const roomList = document.getElementById('roomList');
const form = document.getElementById('roomForm');
const message = document.getElementById('message');

let rooms = [];

// ========================================
// SWEETALERT HELPERS
// ========================================

function showSuccess(title, text) {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: text || 'Operation completed successfully.',
        confirmButtonColor: '#102B59'
    });
}

function showError(error) {
    return Swal.fire({
        icon: 'error',
        title: 'Operation Failed',
        text: error.message || 'Something went wrong.',
        confirmButtonColor: '#102B59'
    });
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
        let errorMessage = result.message || 'Request failed.';

        if (result.errors) {
            errorMessage = Object.values(result.errors).flat().join('\n');
        }

        throw new Error(errorMessage);
    }

    return result;
}

// ========================================
// LOAD ROOMS
// ========================================

async function loadRooms() {
    try {
        const result = await apiRequest('/api/rooms');

        rooms = result.data || [];

        updateTypeFilter();
        renderRooms();

    } catch (error) {
        roomList.textContent = error.message;
    }
}

// ========================================
// UPDATE ROOM TYPE FILTER
// ========================================

function updateTypeFilter() {

    const filter = document.getElementById('typeFilter');
    const selected = filter.value;

    filter.replaceChildren();

    const allOption = document.createElement('option');
    allOption.value = 'all';
    allOption.textContent = 'All Room Types';

    filter.appendChild(allOption);

    const types = [
        ...new Set(rooms.map(room => room.room_type).filter(Boolean))
    ];

    types.forEach(type => {

        const option = document.createElement('option');

        option.value = type;
        option.textContent = type;

        filter.appendChild(option);
    });

    filter.value = types.includes(selected) ? selected : 'all';
}

// ========================================
// RENDER ROOMS
// ========================================

function renderRooms() {

    const search = document.getElementById('searchRoom')
        .value.toLowerCase();

    const type = document.getElementById('typeFilter').value;

    const filtered = rooms.filter(room => {

        const text = [
            room.room_number,
            room.room_name,
            room.location,
            room.room_type
        ].join(' ').toLowerCase();

        return text.includes(search) &&
            (type === 'all' || room.room_type === type);
    });

    roomList.replaceChildren();

    if (!filtered.length) {

        const empty = document.createElement('p');

        empty.className = 'text-center text-gray-500 py-6';
        empty.textContent = 'No rooms found.';

        roomList.appendChild(empty);

        return;
    }

    filtered.forEach(room => {

        const card = document.createElement('div');
        card.className = 'border rounded-xl p-5';

        const title = document.createElement('h3');

        title.className = 'text-lg font-bold text-blue-950';
        title.textContent =
            `${room.room_number} - ${room.room_name || 'Unnamed Room'}`;

        const details = document.createElement('p');

        details.className = 'text-gray-600 mt-2';
        details.textContent =
            `Type: ${room.room_type} | Capacity: ${room.capacity} | Location: ${room.location || 'Not specified'}`;

        const description = document.createElement('p');

        description.className = 'text-gray-500 mt-2';
        description.textContent =
            room.description || 'No description provided.';

        const actions = document.createElement('div');
        actions.className = 'flex gap-2 mt-4';

        // EDIT BUTTON

        const edit = document.createElement('button');

        edit.textContent = 'Edit';
        edit.className =
            'bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg';

        edit.addEventListener('click', () => editRoom(room));

        // DELETE BUTTON

        const remove = document.createElement('button');

        remove.textContent = 'Delete';
        remove.className =
            'bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg';

        remove.addEventListener('click', () => deleteRoom(room));

        actions.append(edit, remove);

        card.append(title, details, description, actions);

        roomList.appendChild(card);
    });
}

// ========================================
// ADD OR UPDATE ROOM
// ========================================

form.addEventListener('submit', async function(event) {

    event.preventDefault();

    const id = document.getElementById('roomId').value;

    const data = {
        room_number: document.getElementById('roomNumber').value.trim(),
        room_name: document.getElementById('roomName').value.trim(),
        location: document.getElementById('location').value.trim(),
        capacity: Number(document.getElementById('capacity').value),
        room_type: document.getElementById('room_type').value,
        description: document.getElementById('description').value.trim()
    };

    const isEditing = Boolean(id);

    const confirmation = await Swal.fire({
        title: isEditing ? 'Update Room?' : 'Add New Room?',
        text: isEditing
            ? `Are you sure you want to update room ${data.room_number}?`
            : `Are you sure you want to add room ${data.room_number}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#102B59',
        cancelButtonColor: '#6B7280',
        confirmButtonText: isEditing ? 'Yes, update room' : 'Yes, add room',
        cancelButtonText: 'Cancel'
    });

    if (!confirmation.isConfirmed) return;

    try {

        const result = await apiRequest(
            isEditing ? `/api/rooms/${id}` : '/api/rooms',
            {
                method: isEditing ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }
        );

        resetForm();

        await showSuccess(
            isEditing ? 'Room Updated!' : 'Room Added!',
            result.message
        );

        await loadRooms();

    } catch (error) {
        showError(error);
    }
});

// ========================================
// EDIT ROOM
// ========================================

function editRoom(room) {

    document.getElementById('roomId').value = room.id;
    document.getElementById('roomNumber').value = room.room_number || '';
    document.getElementById('roomName').value = room.room_name || '';
    document.getElementById('location').value = room.location || '';
    document.getElementById('capacity').value = room.capacity ?? 0;
    document.getElementById('room_type').value = room.room_type || '';
    document.getElementById('description').value = room.description || '';

    document.getElementById('formTitle').textContent = 'Edit Room';
    document.getElementById('saveButton').textContent = 'Update Room';

    document.getElementById('cancelButton').classList.remove('hidden');

    form.scrollIntoView({ behavior: 'smooth' });
}

// ========================================
// RESET FORM
// ========================================

function resetForm() {

    form.reset();

    document.getElementById('roomId').value = '';

    document.getElementById('formTitle').textContent = 'Add New Room';

    document.getElementById('saveButton').textContent = 'Add Room';

    document.getElementById('cancelButton').classList.add('hidden');
}

// ========================================
// CANCEL EDIT
// ========================================

document.getElementById('cancelButton')
    .addEventListener('click', async () => {

        const confirmation = await Swal.fire({
            title: 'Cancel Editing?',
            text: 'Your unsaved changes will be discarded.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#102B59',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, cancel',
            cancelButtonText: 'Continue Editing'
        });

        if (confirmation.isConfirmed) {
            resetForm();
        }
    });

// ========================================
// DELETE ROOM
// ========================================

async function deleteRoom(room) {

    const confirmation = await Swal.fire({
        title: 'Delete Room?',
        text: `Are you sure you want to delete room ${room.room_number}? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete room',
        cancelButtonText: 'Cancel'
    });

    if (!confirmation.isConfirmed) return;

    try {

        const result = await apiRequest(`/api/rooms/${room.id}`, {
            method: 'DELETE'
        });

        await showSuccess('Room Deleted!', result.message);

        await loadRooms();

    } catch (error) {
        showError(error);
    }
}

// ========================================
// SEARCH AND FILTER
// ========================================

document.getElementById('searchRoom')
    .addEventListener('input', renderRooms);

document.getElementById('typeFilter')
    .addEventListener('change', renderRooms);

// ========================================
// INITIALIZE
// ========================================

loadRooms();

</script>

@endsection

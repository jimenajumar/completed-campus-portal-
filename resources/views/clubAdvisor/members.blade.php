@extends('layouts.advisor')

@section('title', 'Club Members - ACES')

@section('content')

<div class="max-w-6xl mx-auto px-6 py-10">

    <div class="mb-8">
        <a href="{{ url('/club-advisor/clubs') }}"
           class="text-sm text-blue-700 hover:underline">
            ← Back to My Assigned Clubs
        </a>

        <h1 class="text-3xl font-bold text-blue-950 mt-4">
            Club Members
        </h1>

        <p class="text-gray-500 mt-2">
            View students who are approved members of this club.
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">

        <div class="flex flex-col md:flex-row md:items-center
                    md:justify-between gap-4 mb-6">

            <div>
                <h2 id="clubName" class="text-xl font-bold text-blue-950">
                    Loading club...
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    <span id="memberCount">0</span> approved members
                </p>
            </div>

            <input
                type="search"
                id="memberSearch"
                placeholder="Search members..."
                class="border border-gray-300 rounded-lg px-4 py-2
                       focus:ring-2 focus:ring-blue-500 outline-none">
        </div>

        <div id="memberMessage" class="text-sm mb-4"></div>

        <div class="overflow-x-auto">

            <table class="w-full text-left">

                <thead class="bg-gray-100 text-gray-600 text-sm">
                    <tr>
                        <th class="p-4">Student Name</th>
                        <th class="p-4">Student ID</th>
                        <th class="p-4">Email Address</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>

                <tbody id="memberTable" class="divide-y">
                    <tr>
                        <td colspan="4" class="p-5 text-center text-gray-500">
                            Loading members...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

</div>

<script>
    const token = sessionStorage.getItem('campus_token');
    const clubId = @json($clubId);

    const memberTable = document.getElementById('memberTable');
    const memberMessage = document.getElementById('memberMessage');
    const memberSearch = document.getElementById('memberSearch');

    let approvedMembers = [];

    async function loadMembers() {
        try {
            const response = await fetch(`/api/clubs/${clubId}/memberships`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to load club members.');
            }

            const memberships = Array.isArray(data) ? data : data.data || [];

            approvedMembers = memberships.filter(
                membership => membership.status === 'approved'
            );

            document.getElementById('memberCount').textContent =
                approvedMembers.length;

            renderMembers(approvedMembers);

        } catch (error) {
            memberMessage.textContent = error.message;
            memberMessage.className = 'text-red-600 mb-4';

            memberTable.innerHTML = `
                <tr>
                    <td colspan="4" class="p-5 text-center text-red-600">
                        Unable to load members.
                    </td>
                </tr>
            `;
        }
    }

    function renderMembers(members) {
        memberTable.replaceChildren();

        if (!members.length) {
            memberTable.innerHTML = `
                <tr>
                    <td colspan="4" class="p-5 text-center text-gray-500">
                        No approved members found.
                    </td>
                </tr>
            `;
            return;
        }

        members.forEach(membership => {
            const student = membership.student || {};

            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';

            const values = [
                student.name || 'N/A',
                student.student_id || 'N/A',
                student.email || 'N/A',
                membership.status || 'N/A'
            ];

            values.forEach(value => {
                const cell = document.createElement('td');
                cell.className = 'p-4 text-sm text-gray-700';
                cell.textContent = value;
                row.appendChild(cell);
            });

            memberTable.appendChild(row);
        });
    }

    memberSearch.addEventListener('input', function () {
        const search = this.value.toLowerCase();

        const filtered = approvedMembers.filter(membership => {
            const student = membership.student || {};

            return [
                student.name,
                student.student_id,
                student.email
            ].some(value => String(value || '').toLowerCase().includes(search));
        });

        renderMembers(filtered);
    });

    loadMembers();
</script>

@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ACES Admin Portal')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F3F5F9] min-h-screen">

    <div class="min-h-screen flex">

        <!-- ADMIN SIDEBAR -->
        <aside class="w-64 min-h-screen bg-[#102B59] text-white
                     flex flex-col shadow-xl">

            <!-- BRANDING -->
            <div class="px-6 py-7 border-b border-blue-800">

                <div class="flex items-center gap-3">

                    <div class="w-12 h-12 rounded-full bg-white
                                border-2 border-[#D6B45B]
                                flex items-center justify-center
                                text-[#102B59] font-bold text-sm">
                        ATCI
                    </div>

                    <div>
                        <h1 class="font-bold text-lg">ACES</h1>

                        <p class="text-xs text-[#D6B45B]">
                            ADMIN PORTAL
                        </p>
                    </div>

                </div>

                <p class="text-xs text-blue-200 mt-4">
                    ACES Tagum College Inc.
                </p>

            </div>


            <!-- NAVIGATION -->
            <nav class="flex-1 px-4 py-6 space-y-2">

                <p class="text-xs uppercase tracking-widest
                          text-blue-300 px-4 mb-4">
                    Administration
                </p>

                <!-- Dashboard -->
                <a href="{{ url('/admin/dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg
                   font-semibold transition
                   {{ request()->is('admin/dashboard') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                    <span>▦</span>
                    Dashboard
                </a>


                <!-- Users -->
                <a href="{{ url('/admin/users') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg
                   font-semibold transition
                   {{ request()->is('admin/users*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                    <span>♙</span>
                    User Management
                </a>


                <!-- Announcements -->
                <a href="{{ url('/admin/announcements') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg
                   font-semibold transition
                   {{ request()->is('admin/announcements*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                    <span>▤</span>
                    Announcements
                </a>


                <!-- Events -->
                <a href="{{ url('/admin/events') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg
                   font-semibold transition
                   {{ request()->is('admin/events*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                    <span>▣</span>
                    Events
                </a>


                <!-- Clubs -->
                <a href="{{ url('/admin/clubs') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg
                   font-semibold transition
                   {{ request()->is('admin/clubs*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                    <span>♧</span>
                    Club Management
                </a>


                <!-- Memberships -->
                <a href="{{ url('/admin/memberships') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg
                   font-semibold transition
                   {{ request()->is('admin/memberships*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                    <span>♧</span>
                    Club Memberships
                </a>


                <!-- Rooms -->
                <a href="{{ url('/admin/rooms') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg
                   font-semibold transition
                   {{ request()->is('admin/rooms*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                    <span>▦</span>
                    Room Management
                </a>


                <!-- Room Assignments -->
                <a href="{{ url('/admin/room-assignments') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg
                   font-semibold transition
                   {{ request()->is('admin/room-assignments*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                    <span>▣</span>
                    Room Assignments
                </a>
                <a href="{{ url('/admin/activity-logs') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg
                    transition {{ request()->is('admin/activity-logs') ? 'bg-[#D6B45B] text-[#102B59] font-semibold' : 'text-white hover:bg-[#173F91]' }}">
                        Activity Logs
                </a>

                <a href="{{ route('admin.settings') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg font-semibold transition {{ request()->routeIs('admin.settings*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">
                    Settings

                </a>


        <!-- Room Reservations -->
    <a href="{{ url('/admin/room-reservations') }}"
   class="flex items-center gap-3 px-4 py-3 rounded-lg
   font-semibold transition
   {{ request()->is('admin/room-reservations*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

    <span>▤</span>
    Room Reservations
</a>



            </nav>


            <!-- LOGOUT -->
            <div class="px-4 py-5 border-t border-blue-800">

                <form id="logoutForm"
                      action="{{ url('/web-logout') }}"
                      method="POST">

                    @csrf

                    <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3
                               rounded-lg text-white font-semibold
                               hover:bg-red-700 transition">

                        <span>↪</span>
                        Logout

                    </button>

                </form>

            </div>

        </aside>


        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 min-w-0">

            <!-- TOP HEADER -->
            <header class="bg-white shadow-sm px-6 py-5
                           flex items-center justify-between">

                <div>
                    <h2 class="text-xl font-bold text-[#102B59]">
                        @yield('page-heading', 'Admin Dashboard')
                    </h2>

                    <p class="text-sm text-gray-500">
                        ACES Tagum College Inc. | Administration Portal
                    </p>
                </div>
<div class="text-right">
    <div id="headerAdminSkeleton"
         class="ml-auto h-5 w-32 animate-pulse rounded-md bg-slate-200">
    </div>

    <p id="headerAdminName"
       class="hidden font-semibold text-[#102B59]">
        Administrator
    </p>

    <p class="text-xs text-gray-500">
        System Administrator
    </p>
</div>


            </header>


            <!-- PAGE CONTENT -->
            <main class="p-5 md:p-8">
                @yield('content')
            </main>

        </div>

    </div>


    <!-- LOGOUT SCRIPT -->
    <script>
    const logoutForm = document.getElementById('logoutForm');

    if (logoutForm) {
        logoutForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const result = await Swal.fire({
                title: 'Log out?',
                text: 'Are you sure you want to end your admin session?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#102B59',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, log out',
                cancelButtonText: 'Stay signed in'
            });

            if (!result.isConfirmed) return;

            const form = this;
            const token = sessionStorage.getItem('campus_token');

            if (token) {
                try {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    });
                } catch (error) {
                    console.error('API logout request failed.');
                }
            }

            sessionStorage.removeItem('campus_token');
            sessionStorage.removeItem('campus_user');

            form.submit();
        });
    }

// Load logged-in Admin information
const adminToken = sessionStorage.getItem('campus_token');

const adminName = document.getElementById('headerAdminName');
const adminSkeleton = document.getElementById('headerAdminSkeleton');

function showAdminName(name) {
    adminName.textContent = name || 'Administrator';

    adminSkeleton.classList.add('hidden');
    adminName.classList.remove('hidden');
}

if (adminToken) {
    fetch('/api/user', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${adminToken}`
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Unable to load Admin information.');
        }

        return response.json();
    })
    .then(user => {
        showAdminName(user.name);
    })
    .catch(error => {
        console.error('Admin information error:', error);
        showAdminName('Administrator');
    });

} else {
    showAdminName('Administrator');
}


</script>
</body>
</html>

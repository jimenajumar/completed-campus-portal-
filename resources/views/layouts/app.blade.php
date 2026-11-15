<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>@yield('title', 'ACES Student Portal')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .slider-viewport {
            overflow: hidden;
            position: relative;
        }

        .slider-track {
            display: flex;
            transition: transform 0.7s ease-in-out;
            width: 100%;
        }

        .campus-slide {
            flex: 0 0 100%;
            width: 100%;
            min-width: 100%;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">

    <!-- TOP HEADER -->

<div class="min-h-screen flex bg-[#F3F5F9]">

    <!-- VERTICAL SIDEBAR -->
    <aside class="w-64 min-h-screen bg-[#102B59] text-white
                 flex flex-col shadow-xl">

        <!-- LOGO AND BRAND -->
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
                        CAMPUS PORTAL
                    </p>
                </div>

            </div>

            <p class="text-xs text-blue-200 mt-4">
                ACES Tagum College Inc.
            </p>

        </div>


        <!-- NAVIGATION BUTTONS -->
        <nav class="flex-1 px-4 py-6 space-y-2">

            <p class="text-xs uppercase tracking-widest
                      text-blue-300 px-4 mb-4">
                Main Menu
            </p>

            <a href="{{ url('/dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               font-semibold transition
               {{ request()->is('dashboard') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                <span>▦</span>
                Dashboard
            </a>

            <a href="{{ url('/events') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               font-semibold transition
               {{ request()->is('events*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                <span>▣</span>
                Events
            </a>

            <a href="{{ url('/announcements') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               font-semibold transition
               {{ request()->is('announcements*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                <span>▤</span>
                Announcements
            </a>

            <a href="{{ url('/clubs') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               font-semibold transition
               {{ request()->is('clubs*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                <span>♧</span>
                Clubs
            </a>

            <a href="{{ url('/rooms') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               font-semibold transition
               {{ request()->is('rooms*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                <span>▣</span>
                Rooms
            </a>

            <a href="{{ url('/profile') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               font-semibold transition
               {{ request()->is('profile*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">

                <span>♙</span>
                Profile
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

        <!-- TOP BAR -->
        <header class="bg-white shadow-sm px-6 py-5
                       flex items-center justify-between">

            <div>
                <h2 class="text-xl font-bold text-[#102B59]">
                    @yield('page-heading', 'Student Dashboard')
                </h2>

                <p class="text-sm text-gray-500">
                    Welcome to ACES Campus Portal
                </p>
            </div>

    <div class="text-right">
        <div id="headerStudentSkeleton"
             class="ml-auto h-5 w-32 animate-pulse rounded-md bg-slate-200">
        </div>

        <p id="headerStudentName"
        class="hidden font-semibold text-[#102B59]">
        </p>

        <p class="text-xs text-gray-500">
            IICT Department
        </p>
</div>



        </header>


        <!-- PAGE CONTENT -->
        <main class="p-5 md:p-8">
            @yield('content')
        </main>

    </div>

</div>


    <!-- SCRIPTS -->
    <script>


// Load logged-in student's information
const campusToken = sessionStorage.getItem('campus_token');

const studentName = document.getElementById('headerStudentName');
const studentSkeleton = document.getElementById('headerStudentSkeleton');

function showStudentName(name) {
    studentName.textContent = name || 'Student';

    studentSkeleton.classList.add('hidden');
    studentName.classList.remove('hidden');
}

if (campusToken) {

    fetch('/api/user', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${campusToken}`
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Unable to load user.');
        }

        return response.json();
    })
    .then(user => {
        showStudentName(user.name);
    })
    .catch(error => {
        console.error('Student information error:', error);
        showStudentName('Student');
    });

} else {
    showStudentName('Student');
}


        // Logout functionality
            document.addEventListener('DOMContentLoaded', function () {

            const logoutForm = document.getElementById('logoutForm');

            if (logoutForm) {
                logoutForm.addEventListener('submit', async function (event) {
                event.preventDefault();

            if (!window.Swal) {
                alert('SweetAlert is not loaded. Please refresh the page.');
                return;
            }

            const result = await Swal.fire({
                title: 'Log out?',
                text: 'Are you sure you want to log out?',
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

        });

</script>

</body>
</html>

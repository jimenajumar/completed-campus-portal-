
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ACES Club Advisor Portal')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F3F5F9] min-h-screen">

<div class="min-h-screen flex">

    <!-- SIDEBAR -->
    <aside class="w-64 min-h-screen bg-[#102B59] text-white flex flex-col shadow-xl">

        <!-- BRANDING -->
        <div class="px-6 py-7 border-b border-blue-800">
            <div class="flex items-center gap-3">

                <div class="w-12 h-12 rounded-full bg-white border-2 border-[#D6B45B]
                            flex items-center justify-center text-[#102B59] font-bold">
                    ATCI
                </div>

                <div>
                    <h1 class="font-bold text-lg">ACES</h1>
                    <p class="text-xs text-[#D6B45B]">CAMPUS PORTAL</p>
                </div>

            </div>

            <p class="text-xs text-blue-200 mt-4">
                CLUB ADVISOR PORTAL
            </p>
        </div>

        <!-- NAVIGATION -->
        <nav class="flex-1 px-4 py-6 space-y-2">

            <p class="text-xs uppercase tracking-widest text-blue-300 px-4 mb-4">
                Main Menu
            </p>

            <a href="{{ url('/club-advisor/dashboard') }}"
               class="block px-4 py-3 rounded-lg font-semibold transition
               {{ request()->is('club-advisor/dashboard') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">
                Dashboard
            </a>

            <a href="{{ url('/club-advisor/clubs') }}"
               class="block px-4 py-3 rounded-lg font-semibold transition
               {{ request()->is('club-advisor/clubs*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">
                My Clubs
            </a>

            <a href="{{ url('/club-advisor/memberships') }}"
               class="block px-4 py-3 rounded-lg font-semibold transition
               {{ request()->is('club-advisor/memberships*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">
                Membership Requests
            </a>


            <a href="{{ url('/club-advisor/announcements') }}"
               class="block px-4 py-3 rounded-lg font-semibold transition
               {{ request()->is('club-advisor/announcements*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">
                Announcements
            </a>

            <a href="{{ url('/profile') }}"
               class="block px-4 py-3 rounded-lg font-semibold transition
               {{ request()->is('profile*') ? 'bg-[#D6B45B] text-[#102B59]' : 'text-white hover:bg-[#173F91]' }}">
                Profile
            </a>

        </nav>

        <!-- LOGOUT -->
        <div class="px-4 py-5 border-t border-blue-800">

            <form id="advisorLogoutForm"
                  action="{{ url('/web-logout') }}"
                  method="POST">
                @csrf

                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-lg
                           text-white font-semibold hover:bg-red-700 transition">
                    ↪ Logout
                </button>
            </form>

        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 min-w-0">

        <!-- TOP HEADER -->
        <header class="bg-white shadow-sm px-6 py-5 flex items-center justify-between">

            <div>
                <h2 class="text-xl font-bold text-[#102B59]">
                    @yield('page-heading', 'Advisor Dashboard')
                </h2>

                <p class="text-sm text-gray-500">
                    ACES Tagum College Inc.
                </p>
            </div>
<div class="text-right">
    <div id="advisorNameSkeleton"
         class="ml-auto h-5 w-32 animate-pulse rounded-md bg-slate-200">
    </div>

    <p id="advisorName"
       class="hidden font-semibold text-[#102B59]">
        {{ Auth::user()->name }}
    </p>

    <p class="text-xs text-gray-500">
        Club Advisor
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
document.addEventListener('DOMContentLoaded', function () {

    const advisorLogoutForm = document.getElementById('advisorLogoutForm');

    if (advisorLogoutForm) {
        advisorLogoutForm.addEventListener('submit', async function(event) {

            event.preventDefault();

            if (!window.Swal) {
                alert('SweetAlert is not loaded. Please refresh the page.');
                return;
            }

            const result = await Swal.fire({
                title: 'Log out?',
                text: 'Are you sure you want to end your session?',
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
document.addEventListener('DOMContentLoaded', function () {
    const skeleton = document.getElementById('advisorNameSkeleton');
    const name = document.getElementById('advisorName');

    skeleton.classList.add('hidden');
    name.classList.remove('hidden');
});
</script>

</body>
</html>

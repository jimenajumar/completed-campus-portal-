<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Login - ACES Campus Portal</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F3F5F9]">

    <div class="min-h-screen flex flex-row">

        <!-- LEFT: CAMPUS BRANDING -->
        <section
            class="relative w-1/2 min-h-[280px] lg:min-h-screen
                   flex items-center justify-center bg-[#102B59]
                   bg-cover bg-center"
            style="background-image:
            linear-gradient(rgba(16,43,89,0.82), rgba(16,43,89,0.88)),
            url('{{ asset('images/aces building.png') }}');">

            <div class="relative z-10 max-w-xl px-8 py-12 lg:px-16 text-white">

                <!-- LOGO -->
                <div class="flex items-center gap-4 mb-10">
                    <div class="bg-white rounded-full p-2
                                w-16 h-16 flex items-center justify-center
                                border-2 border-[#D6B45B]">

                        <img src="{{ asset('images/logo.png') }}"
                             alt="ACES Logo"
                             class="w-full h-full object-contain">
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold tracking-wide">
                            ACES
                        </h1>

                        <p class="text-[#D6B45B] text-xs
                                  font-semibold tracking-[3px]">
                            CAMPUS PORTAL
                        </p>
                    </div>
                </div>

                <!-- WELCOME TEXT -->
                <div class="border-l-4 border-[#D6B45B] pl-6">

                    <p class="text-[#D6B45B] uppercase
                              tracking-[3px] text-sm font-semibold mb-4">
                        ACES Tagum College Inc.
                    </p>

                    <h2 class="text-4xl lg:text-5xl
                               font-bold leading-tight mb-6">
                        Your Campus.
                        <span class="text-[#D6B45B]">
                            Your Community.
                        </span>
                    </h2>

                    <p class="text-blue-100 text-base lg:text-lg
                              leading-relaxed max-w-md">
                        Stay connected with your campus.
                        Access announcements, events,
                        clubs, and important student information
                        in one place.
                    </p>
                </div>

                <!-- BOTTOM TEXT -->
                <div class="mt-12 hidden lg:block">
                    <div class="w-16 h-1 bg-[#D6B45B] mb-4"></div>

                    <p class="text-sm text-blue-200">
                        Welcome to the ACES Campus Portal.
                    </p>
                </div>

            </div>

            <!-- DECORATIVE ACCENT -->
            <div class="absolute bottom-0 right-0
                        w-40 h-40 bg-[#D6B45B]/10
                        rounded-tl-full">
            </div>

        </section>


        <!-- RIGHT: LOGIN FORM -->
        <section class="w-1/2 flex items-center justify-center
                        px-6 py-12 lg:px-16">

            <div class="w-full max-w-md">

                <!-- FORM HEADING -->
                <div class="mb-9">

                    <p class="text-sm font-semibold uppercase
                              tracking-[2px] text-[#173F91] mb-3">
                        Student Portal
                    </p>

                    <h2 class="text-3xl lg:text-4xl
                               font-bold text-[#102B59]">
                        Welcome Back!
                    </h2>

                    <p class="text-gray-500 mt-3">
                        Sign in to access your campus account.
                    </p>

                </div>


                <!-- LOGIN CARD -->
                <div class="bg-white rounded-2xl
                            shadow-xl shadow-blue-950/5
                            border border-gray-100 p-7 lg:p-9">

                    <form id="loginForm" class="space-y-6">

                        <!-- LOGIN MESSAGE -->
                        <div id="loginMessage"
                             class="hidden text-sm text-center
                                    rounded-lg p-3">
                        </div>


                        <!-- EMAIL -->
                        <div>

                            <label for="email"
                                   class="block text-sm font-semibold
                                          text-[#102B59] mb-2">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email address"
                                autocomplete="username"
                                required
                                class="w-full border border-gray-200
                                       bg-[#F8FAFC] rounded-xl
                                       px-4 py-3.5 text-gray-700
                                       outline-none transition
                                       focus:border-[#173F91]
                                       focus:ring-2
                                       focus:ring-[#173F91]/20">
                        </div>


                        <!-- PASSWORD -->
                        <div>

                            <label for="password"
                                   class="block text-sm font-semibold
                                          text-[#102B59] mb-2">
                                Password
                            </label>

                            <div class="relative">

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required
                                    class="w-full border border-gray-200
                                           bg-[#F8FAFC] rounded-xl
                                           px-4 py-3.5 pr-20
                                           text-gray-700 outline-none
                                           transition focus:border-[#173F91]
                                           focus:ring-2
                                           focus:ring-[#173F91]/20">

                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="absolute right-4 top-1/2
                                           -translate-y-1/2
                                           text-sm font-semibold
                                           text-[#173F91]
                                           hover:text-[#102B59]">

                                    Show

                                </button>

                            </div>
                        </div>


                        <!-- LOGIN BUTTON -->
                        <button
                            type="submit"
                            id="loginButton"
                            class="w-full bg-[#102B59]
                                   hover:bg-[#173F91]
                                   text-white py-3.5 rounded-xl
                                   font-bold tracking-wide
                                   transition duration-300
                                   shadow-lg shadow-blue-950/15
                                   hover:-translate-y-0.5">

                            
                                Log In to Your Account

                        </button>

                    </form>

                </div>


                <!-- FOOTER -->
                <div class="text-center mt-8">

                    <div class="flex items-center justify-center
                                gap-3 mb-4">

                        <div class="h-px bg-gray-300 flex-1"></div>

                        <span class="text-xs text-gray-400
                                     uppercase tracking-widest">
                            ACES
                        </span>

                        <div class="h-px bg-gray-300 flex-1"></div>

                    </div>

                    <p class="text-xs text-gray-500">
                        ACES Tagum College Inc.
                    </p>

                    <p class="text-xs text-gray-400 mt-2">
                        © {{ date('Y') }} ACES Campus Portal.
                        All rights reserved.
                    </p>

                </div>

            </div>

        </section>

    </div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const isPasswordHidden = passwordInput.type === 'password';

        passwordInput.type = isPasswordHidden ? 'text' : 'password';
        togglePassword.textContent = isPasswordHidden ? 'Hide' : 'Show';
    });
</script>
    <!-- log in -->
    <script>
document.getElementById('loginForm')
    .addEventListener('submit', async function(e) {

    e.preventDefault();

    const email = document.querySelector('[name="email"]').value;
    const password = document.querySelector('[name="password"]').value;
    const loginButton = document.getElementById('loginButton');

    loginButton.disabled = true;
    loginButton.textContent = 'Signing in...';

    try {
        const response = await fetch('/web-login', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                email,
                password
            })
        });

        const data = await response.json();

        if (!response.ok) {
            await Swal.fire({
                title: 'Login Failed!',
                text: data.message || 'Please check your email and password.',
                icon: 'error',
                confirmButtonColor: '#102B59'
            });
            return;
        }

        sessionStorage.setItem('campus_token', data.token);
        sessionStorage.setItem('campus_user', JSON.stringify(data.user));

        await Swal.fire({
            title: 'Welcome!',
            text: 'Login successful. Redirecting...',
            icon: 'success',
            confirmButtonColor: '#102B59',
            timer: 1500,
            showConfirmButton: false
        });

        window.location.href = data.redirect;

    } catch(error) {

        await Swal.fire({
            title: 'Connection Error!',
            text: 'Unable to connect to the server. Please try again.',
            icon: 'error',
            confirmButtonColor: '#102B59'
        });

    } finally {
        loginButton.disabled = false;
        loginButton.textContent = 'Sign In to Your Account';
    }

});
</script>


</body>
</html>

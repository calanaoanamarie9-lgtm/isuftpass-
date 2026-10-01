<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ISUFSTPASS | Student Portal Sign In</title>

    <link rel="icon"
          type="image/png"
          href="{{ asset('img/isufstpass-logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.pwa-head')
</head>

<body class="font-sans antialiased bg-slate-100 min-h-screen">

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">

        <!-- Main Login Container -->
        <div class="w-full max-w-6xl min-h-[650px] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">

            <!-- =====================================================
                 LEFT SIDE - UNIVERSITY INFORMATION
            ====================================================== -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-white relative overflow-hidden">

                <!-- Decorative circles -->
                <div class="absolute -top-24 -left-24 w-72 h-72 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-32 -right-20 w-96 h-96 bg-blue-400/10 rounded-full"></div>

                <div class="relative z-10 flex flex-col justify-between p-12 xl:p-16 w-full">

                    <!-- Logo -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg">
                            <img
                                src="{{ asset('img/isufstpass-logo.png') }}"
                                alt="ISUFSTPASS Logo"
                                class="w-12 h-12 object-contain"
                            >
                        </div>

                        <div>
                            <p class="font-bold text-xl tracking-wide">
                                ISUFSTPASS
                            </p>

                            <p class="text-blue-200 text-sm">
                                Iloilo State University
                            </p>
                        </div>
                    </div>

                    <!-- Main Text -->
                    <div class="max-w-lg my-12">

                        <p class="text-blue-200 font-semibold text-sm uppercase tracking-[0.2em] mb-5">
                            University Transaction Portal
                        </p>

                        <h1 class="text-5xl xl:text-6xl font-extrabold leading-tight">
                            Online
                            <span class="block text-blue-300">
                                Transactions.
                            </span>

                            <span class="block">
                                Made Simple.
                            </span>
                        </h1>

                        <p class="mt-7 text-blue-100 text-lg leading-8 max-w-md">
                            ISUFSTPASS provides a convenient, organized, and secure
                            platform for managing university transactions, document
                            requests, and appointment bookings.
                        </p>

                    </div>

                    <!-- Features -->
                    <div class="grid grid-cols-3 gap-4">

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                            <div class="text-2xl mb-2">📄</div>
                            <p class="text-sm font-semibold">
                                Documents
                            </p>
                            <p class="text-xs text-blue-200 mt-1">
                                Request online
                            </p>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                            <div class="text-2xl mb-2">📅</div>
                            <p class="text-sm font-semibold">
                                Appointments
                            </p>
                            <p class="text-xs text-blue-200 mt-1">
                                Book easily
                            </p>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                            <div class="text-2xl mb-2">🔐</div>
                            <p class="text-sm font-semibold">
                                Secure
                            </p>
                            <p class="text-xs text-blue-200 mt-1">
                                QR-enabled
                            </p>
                        </div>

                    </div>

                </div>
            </div>


            <!-- =====================================================
                 RIGHT SIDE - LOGIN FORM
            ====================================================== -->
            <div class="w-full lg:w-1/2 flex items-center justify-center bg-white px-6 py-10 sm:px-10 lg:px-14 xl:px-20">

                <div class="w-full max-w-md">

                    <!-- Mobile Logo -->
                    <div class="flex lg:hidden items-center justify-center gap-3 mb-6">

                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                            <img
                                src="{{ asset('img/isufstpass-logo.png') }}"
                                alt="ISUFSTPASS"
                                class="w-10 h-10 object-contain"
                            >
                        </div>

                        <div>
                            <p class="font-bold text-lg text-gray-900">
                                ISUFSTPASS
                            </p>

                            <p class="text-xs text-gray-500">
                                University Transaction Portal
                            </p>
                        </div>

                    </div>


                    <!-- =================================================
                         BACK TO HOME BUTTON
                    ================================================== -->
                    <div class="mb-8">

                        <a
                            href="{{ url('/') }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-600 transition-colors group"
                        >

                            <svg
                                class="w-5 h-5 transition-transform group-hover:-translate-x-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                />
                            </svg>

                            Back to Home

                        </a>

                    </div>


                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif


                    <!-- Heading -->
                    <div class="mb-8">

                        <p class="text-blue-600 font-bold text-sm uppercase tracking-wider mb-2">
                            WELCOME!
                        </p>

                        <h2 class="text-4xl font-extrabold text-gray-900">
                            Sign in
                        </h2>

                        <p class="mt-3 text-gray-500 leading-6">
                            Sign in to access your
                            <span class="font-semibold text-gray-700">
                                ISUFSTPASS
                            </span>
                            account.
                        </p>

                    </div>


                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">

                        @csrf

                        <!-- Email -->
                        <div>

                            <label
                                for="email"
                                class="block text-sm font-semibold text-gray-800 mb-2"
                            >
                                Email Address
                            </label>

                            <div class="relative">

                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">

                                    <svg
                                        class="w-5 h-5 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>

                                </div>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Enter your email address"
                                    class="block w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                            </div>

                            @if ($errors->get('email'))
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $errors->first('email') }}
                                </p>
                            @endif

                        </div>


                        <!-- Password -->
                        <div>

                            <div class="flex items-center justify-between mb-2">

                                <label
                                    for="password"
                                    class="block text-sm font-semibold text-gray-800"
                                >
                                    Password
                                </label>

                                @if (Route::has('password.request'))
                                    <a
                                        href="{{ route('password.request') }}"
                                        class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                                    >
                                        Forgot password?
                                    </a>
                                @endif

                            </div>

                            <div class="relative">

                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">

                                    <svg
                                        class="w-5 h-5 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        />
                                    </svg>

                                </div>

                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    class="block w-full pl-12 pr-12 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                <button
                                    type="button"
                                    data-no-confirm
                                    id="toggle-password"
                                    onclick="togglePassword('toggle-password', 'password')"
                                    aria-label="Show or hide password"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                    <svg class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>

                            </div>

                            @if ($errors->get('password'))
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $errors->first('password') }}
                                </p>
                            @endif

                        </div>


                        <!-- Login Button -->
                        <button
                            type="submit"
                            data-no-confirm
                            class="w-full flex items-center justify-center gap-3 py-3.5 px-6 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5"
                        >

                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4m-5-4l5-5m0 0l-5-5m5 5H3"
                                />
                            </svg>

                            Sign In

                        </button>

                    </form>


                    <!-- Register -->
                    @if (Route::has('register'))

                        <div class="mt-8 text-center">

                            <p class="text-sm text-gray-500">

                                Don't have an account?

                                <a
                                    href="{{ route('register') }}"
                                    class="font-semibold text-blue-600 hover:text-blue-800"
                                >
                                    Create an account
                                </a>

                            </p>

                        </div>

                    @endif


                    <!-- Footer -->
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">

                        <p class="text-xs text-gray-400">
                            © {{ date('Y') }} ISUFSTPASS
                        </p>

                        <p class="text-xs text-gray-400 mt-1">
                            QR Code-Based Transaction Management System
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>
<script>
    function togglePassword(buttonId, inputId) {
        var input = document.getElementById(inputId);
        var button = document.getElementById(buttonId);
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.querySelectorAll('svg').forEach(function (svg) {
            svg.classList.toggle('hidden');
        });
    }
</script>
</html>
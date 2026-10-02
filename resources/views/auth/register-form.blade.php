<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ match ($userType) {
        'student' => 'Student',
        'office' => 'Office / Staff',
        default => 'Other',
    } }} Registration | ISUFSTPASS</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.pwa-head')
</head>

<body class="font-sans antialiased bg-slate-100 min-h-screen">

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">

        <!-- Main Container -->
        <div class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">

            <!-- =====================================================
                 LEFT SIDE - UNIVERSITY INFORMATION
            ====================================================== -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-white relative overflow-hidden">

                <!-- Decorative Circles -->
                <div class="absolute -top-28 -left-28 w-80 h-80 bg-white/10 rounded-full"></div>

                <div class="absolute -bottom-40 -right-28 w-[28rem] h-[28rem] bg-blue-400/10 rounded-full"></div>

                <div class="absolute top-1/2 -right-24 w-48 h-48 bg-indigo-400/10 rounded-full"></div>


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
                            <p class="font-extrabold text-xl tracking-wide">
                                ISUFSTPASS
                            </p>

                            <p class="text-blue-200 text-sm">
                                Iloilo State University
                            </p>
                        </div>

                    </div>


                    <!-- Main Message -->
                    <div class="max-w-lg my-12">

                        <p class="text-blue-200 font-semibold text-sm uppercase tracking-[0.2em] mb-5">
                            University Transaction Portal
                        </p>

                        <h1 class="text-5xl xl:text-6xl font-extrabold leading-tight">

                            One Account.

                            <span class="block text-blue-300">
                                All Transactions.
                            </span>

                        </h1>

                        <p class="mt-7 text-blue-100 text-lg leading-8 max-w-md">
                            Create your ISUFSTPASS account and enjoy a convenient,
                            organized, and secure way to manage university
                            transactions.
                        </p>

                    </div>


                    <!-- Features -->
                    <div class="grid grid-cols-3 gap-4">

                        <!-- Documents -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">

                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-blue-200"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />

                                </svg>
                            </div>

                            <p class="text-sm font-semibold">
                                Documents
                            </p>

                            <p class="text-xs text-blue-200 mt-1">
                                Request online
                            </p>

                        </div>


                        <!-- Appointments -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">

                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mb-3">

                                <svg class="w-5 h-5 text-blue-200"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />

                                </svg>

                            </div>

                            <p class="text-sm font-semibold">
                                Appointments
                            </p>

                            <p class="text-xs text-blue-200 mt-1">
                                Book easily
                            </p>

                        </div>


                        <!-- QR -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">

                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mb-3">

                                <svg class="w-5 h-5 text-blue-200"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2m2 0h2m-6 4h2m2 0h2m-6 2h6"
                                    />

                                </svg>

                            </div>

                            <p class="text-sm font-semibold">
                                QR Enabled
                            </p>

                            <p class="text-xs text-blue-200 mt-1">
                                Fast & secure
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 RIGHT SIDE - REGISTER FORM
            ====================================================== -->
            <div class="w-full lg:w-1/2 flex items-center justify-center bg-white px-6 py-10 sm:px-10 lg:px-14 xl:px-20">

                <div class="w-full max-w-md">


                    <!-- Top Navigation -->
                    <div class="flex items-center justify-between mb-8">

                        <!-- Logo -->
                        <div class="flex items-center gap-2">

                            <img
                                src="{{ asset('img/isufstpass-logo.png') }}"
                                alt="ISUFSTPASS"
                                class="w-9 h-9 object-contain"
                            >

                            <span class="font-bold text-gray-800">
                                ISUFSTPASS
                            </span>

                        </div>


                        <!-- Change Type -->
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-blue-600 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>

                            Change Type
                        </a>

                    </div>


                    <!-- Mobile Logo -->
                    <div class="flex lg:hidden items-center justify-center mb-7">

                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center">

                            <img
                                src="{{ asset('img/isufstpass-logo.png') }}"
                                alt="ISUFSTPASS"
                                class="w-12 h-12 object-contain"
                            >

                        </div>

                    </div>


                    <!-- Type Badge -->
                    <div class="mb-7">

                        @if ($userType === 'student')

                            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-full">
                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        d="M12 14l9-5-9-5-9 5 9 5z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                    />
                                    <path
                                        d="M12 14v7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                    />
                                </svg>

                                ISUFST Student
                            </span>

                        @elseif ($userType === 'office')

                            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full">
                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 10h.01M15 10h.01M9 13h.01M15 13h.01"
                                    />
                                </svg>

                                Office / Staff
                            </span>

                        @else

                            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full">
                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M7 20v-2a3 3 0 015.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>

                                Alumni, Guest,
                                Parent/Guardian
                            </span>

                        @endif

                    </div>


                    <!-- Heading -->
                    <div class="mb-7">

                        <h2 class="text-4xl font-extrabold text-gray-900">
                            {{ match ($userType) {
                                'student' => 'Student',
                                'office' => 'Office / Staff',
                                default => 'Other',
                            } }} Registration
                        </h2>

                        <p class="mt-3 text-gray-500 leading-6">
                            @if ($userType === 'student')

                                Register for an
                                <span class="font-semibold text-gray-700">
                                    ISUFSTPASS
                                </span>
                                account to access university services.

                            @elseif ($userType === 'office')

                                Create an
                                <span class="font-semibold text-gray-700">
                                    ISUFSTPASS
                                </span>
                                account for your office. An administrator has to approve
                                the application before you can sign in.

                            @else

                                Create an
                                <span class="font-semibold text-gray-700">
                                    ISUFSTPASS
                                </span>
                                account as an alumni, guest, or parent/guardian.

                            @endif
                        </p>

                    </div>


                    <!-- Register Form -->
                    <form
                        method="POST"
                        action="{{ route('register') }}"
                        class="space-y-5"
                    >

                        @csrf

                        <input type="hidden" name="user_type" value="{{ $userType }}">


                        @if ($userType === 'office')

                            {{-- Office --}}
                            <div>

                                <label
                                    for="office"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Office
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
                                                d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 10h.01M15 10h.01M9 13h.01M15 13h.01"
                                            />
                                        </svg>

                                    </div>

                                    <select
                                        id="office"
                                        name="office"
                                        required
                                        class="block w-full pl-12 pr-10 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none appearance-none"
                                    >
                                        <option value="" disabled {{ old('office') ? '' : 'selected' }}>
                                            Select your office
                                        </option>

                                        @foreach ($offices as $officeOption)

                                            <option value="{{ $officeOption->value }}" {{ old('office') === $officeOption->value ? 'selected' : '' }}>
                                                {{ $officeOption->label() }}
                                            </option>

                                        @endforeach
                                    </select>

                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>

                                </div>

                                @if ($errors->get('office'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('office') }}
                                    </p>

                                @endif

                            </div>


                            {{-- Position / Designation --}}
                            <div>

                                <label
                                    for="position"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Position / Designation
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
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>

                                    </div>

                                    <input
                                        id="position"
                                        name="position"
                                        type="text"
                                        value="{{ old('position') }}"
                                        required
                                        autocomplete="organization-title"
                                        placeholder="e.g. Administrative Aide III"
                                        class="block w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                    >

                                </div>

                                @if ($errors->get('position'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('position') }}
                                    </p>

                                @endif

                            </div>


                            {{-- Contact Number --}}
                            <div>

                                <label
                                    for="contact_number"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Contact Number
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
                                                d="M3 5a2 2 0 012-2h2.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V13a2 2 0 01-2 2H9a12 12 0 01-8-8V5z"
                                            />
                                        </svg>

                                    </div>

                                    <input
                                        id="contact_number"
                                        name="contact_number"
                                        type="text"
                                        value="{{ old('contact_number') }}"
                                        required
                                        autocomplete="tel"
                                        placeholder="e.g. 09171234567"
                                        class="block w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                    >

                                </div>

                                @if ($errors->get('contact_number'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('contact_number') }}
                                    </p>

                                @endif

                            </div>


                            {{-- Approval notice --}}
                            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 shrink-0 text-indigo-600 mt-0.5"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-indigo-900/80 leading-6">
                                        Your application will be reviewed by an administrator.
                                        You will only be able to sign in once it is approved.
                                    </p>
                                </div>
                            </div>

                        @endif


                        @if ($userType === 'other')

                            <!-- Registration Type -->
                            <div>

                                <label
                                    for="registration_type"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Register as
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
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>

                                    </div>

                                    <select
                                        id="registration_type"
                                        name="registration_type"
                                        required
                                        class="block w-full pl-12 pr-10 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none appearance-none"
                                    >
                                        <option value="" disabled {{ old('registration_type') ? '' : 'selected' }}>
                                            Select Registration Type
                                        </option>

                                        @foreach (['alumni' => 'Alumni', 'guest' => 'Guest', 'parent' => 'Parent/Guardian'] as $value => $label)

                                            <option value="{{ $value }}" {{ old('registration_type') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>

                                        @endforeach
                                    </select>

                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>

                                </div>

                                @if ($errors->get('registration_type'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('registration_type') }}
                                    </p>

                                @endif

                            </div>

                        @endif


                        <!-- Name -->
                        <div>

                            <label
                                for="name"
                                class="block text-sm font-semibold text-gray-800 mb-2"
                            >
                                Full Name
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
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />

                                    </svg>

                                </div>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name') }}"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Enter your full name"
                                    class="block w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                            </div>

                            @if ($errors->get('name'))

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $errors->first('name') }}
                                </p>

                            @endif

                        </div>


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

                            <label
                                for="password"
                                class="block text-sm font-semibold text-gray-800 mb-2"
                            >
                                Password
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
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        />

                                    </svg>

                                </div>

                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Create a password"
                                    class="block w-full pl-12 pr-12 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                <button
                                    type="button"
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


                        <!-- Confirm Password -->
                        <div>

                            <label
                                for="password_confirmation"
                                class="block text-sm font-semibold text-gray-800 mb-2"
                            >
                                Confirm Password
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
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 5c-2.31 0-4.456.654-6.22 1.784M12 19a9 9 0 009-9m-9 9a9 9 0 01-9-9"
                                        />

                                    </svg>

                                </div>

                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Confirm your password"
                                    class="block w-full pl-12 pr-12 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                <button
                                    type="button"
                                    id="toggle-password-confirm"
                                    onclick="togglePassword('toggle-password-confirm', 'password_confirmation')"
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

                            @if ($errors->get('password_confirmation'))

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $errors->first('password_confirmation') }}
                                </p>

                            @endif

                        </div>


                        <!-- Register Button -->
                        <button
                            type="submit"
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
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM5 20a7 7 0 0114 0"
                                />

                            </svg>

                            Create Account

                        </button>

                    </form>


                    <!-- Login -->
                    <div class="mt-7 text-center">

                        <p class="text-sm text-gray-500">

                            Already have an account?

                            <a
                                href="{{ route('login') }}"
                                class="font-semibold text-blue-600 hover:text-blue-800"
                            >
                                Sign In
                            </a>

                        </p>

                    </div>


                    <!-- Back to Home -->
                    <div class="mt-6 flex justify-center">

                        <a
                            href="{{ url('/') }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-600 transition-colors"
                        >

                            <svg
                                class="w-4 h-4"
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


                    <!-- Footer -->
                    <div class="mt-7 pt-5 border-t border-gray-100 text-center">

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
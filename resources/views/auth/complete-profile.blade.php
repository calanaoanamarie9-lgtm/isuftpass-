<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $isStudent = $user->studentProfile !== null;
        $type = $isStudent ? 'student' : $user->registration_type;
        $profile = $isStudent ? $user->studentProfile : $user;
        $profileTypeLabel = match ($type) {
            'student' => 'ISUFST Student',
            'alumni' => 'Alumni',
            'guest' => 'Guest',
            'parent' => 'Parent',
            default => 'Other',
        };
    @endphp

    <title>Complete Personal Details | ISUFSTPASS</title>

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
                                Iloilo State University of Fisheries Science and Technology
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
                            Complete your personal details to finish setting up
                            your ISUFSTPASS account.
                        </p>

                    </div>


                    <!-- Steps -->
                    <div class="space-y-4">

                        <!-- Step 1 -->
                        <div class="flex items-center gap-4">

                            <div class="w-9 h-9 rounded-full bg-white/20 border border-white/30 backdrop-blur-sm flex items-center justify-center text-xs font-bold">
                                1
                            </div>

                            <div>
                                <p class="text-sm font-semibold">
                                    Create Your Account
                                </p>

                                <p class="text-xs text-blue-200">
                                    Registration details
                                </p>
                            </div>

                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-center gap-4">

                            <div class="w-9 h-9 rounded-full bg-white border border-white/30 flex items-center justify-center text-xs font-bold text-blue-700">
                                2
                            </div>

                            <div>
                                <p class="text-sm font-semibold">
                                    Complete Personal Details
                                </p>

                                <p class="text-xs text-blue-200">
                                    Your information
                                </p>
                            </div>

                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-center gap-4 opacity-60">

                            <div class="w-9 h-9 rounded-full bg-white/10 border border-white/30 backdrop-blur-sm flex items-center justify-center text-xs font-bold">
                                3
                            </div>

                            <div>
                                <p class="text-sm font-semibold">
                                    Verify Email
                                </p>

                                <p class="text-xs text-blue-200">
                                    Confirm your account
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 RIGHT SIDE - PERSONAL DETAILS FORM
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


                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-red-600 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                    />
                                </svg>

                                Log Out
                            </button>

                        </form>

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
                    <div class="mb-7 flex items-center gap-3">

                        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-full">
                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>

                            {{ $profileTypeLabel }}
                        </span>

                    </div>


                    <!-- Heading -->
                    <div class="mb-7">

                        <h2 class="text-4xl font-extrabold text-gray-900">
                            Complete Personal Details
                        </h2>

                        <p class="mt-3 text-gray-500 leading-6">
                            {{ $user->name }}, please provide the following
                            information to finish your
                            <span class="font-semibold text-gray-700">
                                ISUFSTPASS
                            </span>
                            account.
                        </p>

                    </div>


                    <!-- Personal Details Form -->
                    <form
                        method="POST"
                        action="{{ route('complete-profile') }}"
                        class="space-y-5"
                    >

                        @csrf


                        @if ($type === 'student')

                            <!-- Student ID -->
                            <div>

                                <label
                                    for="student_id"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Student ID <span class="text-gray-400 font-normal">(optional)</span>
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
                                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"
                                            />
                                        </svg>

                                    </div>

                                    <input
                                        id="student_id"
                                        name="student_id"
                                        type="text"
                                        value="{{ old('student_id', $profile->student_id) }}"
                                        autocomplete="off"
                                        placeholder="e.g. 2024-12345"
                                        class="block w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                    >

                                </div>

                                @if ($errors->get('student_id'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('student_id') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Contact Number -->
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
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                            />
                                        </svg>

                                    </div>

                                    <input
                                        id="contact_number"
                                        name="contact_number"
                                        type="tel"
                                        value="{{ old('contact_number', $profile->contact_number) }}"
                                        required
                                        autocomplete="tel"
                                        placeholder="e.g. 0917 123 4567"
                                        class="block w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                    >

                                </div>

                                @if ($errors->get('contact_number'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('contact_number') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Address -->
                            <div>

                                <label
                                    for="address"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Address <span class="text-gray-400 font-normal">(optional)</span>
                                </label>

                                <input
                                    id="address"
                                    name="address"
                                    type="text"
                                    value="{{ old('address', $profile->address) }}"
                                    autocomplete="street-address"
                                    placeholder="e.g. Barotac Nuevo, Iloilo"
                                    class="block w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                @if ($errors->get('address'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('address') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Course -->
                            <div>

                                <label
                                    for="course"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Course <span class="text-gray-400 font-normal">(optional)</span>
                                </label>

                                <div class="relative">

                                    <select
                                        id="course"
                                        name="course"
                                        autocomplete="off"
                                        class="block w-full px-4 pr-10 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none appearance-none"
                                    >
                                        <option value="" {{ old('course', $profile->course) ? '' : 'selected' }}>
                                            Select College
                                        </option>

                                        @foreach (\App\Models\StudentProfile::COLLEGES as $college)

                                            <option value="{{ $college }}" {{ old('course', $profile->course) === $college ? 'selected' : '' }}>
                                                {{ $college }}
                                            </option>

                                        @endforeach
                                    </select>

                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>

                                </div>

                                @if ($errors->get('course'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('course') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Year Level -->
                            <div>

                                <label
                                    for="year_level"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Year Level <span class="text-gray-400 font-normal">(optional)</span>
                                </label>

                                <div class="relative">

                                    <select
                                        id="year_level"
                                        name="year_level"
                                        class="block w-full px-4 pr-10 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none appearance-none"
                                    >
                                        <option value="" {{ old('year_level', $profile->year_level) ? '' : 'selected' }}>
                                            Select Year Level
                                        </option>

                                        @foreach (\App\Models\StudentProfile::YEAR_LEVELS as $year)

                                            <option value="{{ $year }}" {{ old('year_level', $profile->year_level) === $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>

                                        @endforeach
                                    </select>

                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>

                                </div>

                                @if ($errors->get('year_level'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('year_level') }}
                                    </p>

                                @endif

                            </div>

                        @endif


                        @if ($type === 'alumni')

                            <!-- Alumni Details -->
                            <p class="text-xs font-bold uppercase tracking-widest text-blue-700 pt-1">
                                Alumni Details
                            </p>

                            <!-- Student ID -->
                            <div>

                                <label
                                    for="student_id"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Student ID <span class="text-gray-400 font-normal">(optional)</span>
                                </label>

                                <input
                                    id="student_id"
                                    name="student_id"
                                    type="text"
                                    value="{{ old('student_id', $profile->student_id) }}"
                                    autocomplete="off"
                                    placeholder="e.g. 2019-54321"
                                    class="block w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                @if ($errors->get('student_id'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('student_id') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Course -->
                            <div>

                                <label
                                    for="course"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Course <span class="text-gray-400 font-normal">(optional)</span>
                                </label>

                                <input
                                    id="course"
                                    name="course"
                                    type="text"
                                    value="{{ old('course', $profile->course) }}"
                                    autocomplete="off"
                                    placeholder="e.g. Bachelor of Science in Information Systems"
                                    class="block w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                @if ($errors->get('course'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('course') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Year Graduated -->
                            <div>

                                <label
                                    for="year_graduated"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Year Graduated
                                </label>

                                <input
                                    id="year_graduated"
                                    name="year_graduated"
                                    type="text"
                                    value="{{ old('year_graduated', $profile->year_graduated) }}"
                                    required
                                    maxlength="4"
                                    autocomplete="off"
                                    placeholder="e.g. 2024"
                                    class="block w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                @if ($errors->get('year_graduated'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('year_graduated') }}
                                    </p>

                                @endif

                            </div>

                        @endif


                        @if ($type === 'guest')

                            <!-- Visitor Information -->
                            <p class="text-xs font-bold uppercase tracking-widest text-blue-700 pt-1">
                                Visitor Information
                            </p>

                            <!-- Organization -->
                            <div>

                                <label
                                    for="organization"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Organization <span class="text-gray-400 font-normal">(optional)</span>
                                </label>

                                <input
                                    id="organization"
                                    name="organization"
                                    type="text"
                                    value="{{ old('organization', $profile->organization) }}"
                                    autocomplete="off"
                                    placeholder="e.g. DICT Region VI"
                                    class="block w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                @if ($errors->get('organization'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('organization') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Address -->
                            <div>

                                <label
                                    for="address"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Address <span class="text-gray-400 font-normal">(optional)</span>
                                </label>

                                <input
                                    id="address"
                                    name="address"
                                    type="text"
                                    value="{{ old('address', $profile->address) }}"
                                    autocomplete="street-address"
                                    placeholder="e.g. Barotac Nuevo, Iloilo"
                                    class="block w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                @if ($errors->get('address'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('address') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Purpose -->
                            <div>

                                <label
                                    for="purpose"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Purpose for Using ISUFSTPASS
                                </label>

                                <div class="relative">

                                    <select
                                        id="purpose"
                                        name="purpose"
                                        required
                                        class="block w-full px-4 pr-10 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none appearance-none"
                                    >
                                        <option value="" disabled {{ old('purpose', $profile->purpose) ? '' : 'selected' }}>
                                            Select Purpose
                                        </option>

                                        @foreach (\App\Http\Controllers\Auth\CompleteProfileController::GUEST_PURPOSES as $value => $label)

                                            <option value="{{ $value }}" {{ old('purpose', $profile->purpose) === $value ? 'selected' : '' }}>
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

                                @if ($errors->get('purpose'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('purpose') }}
                                    </p>

                                @endif

                            </div>

                        @endif


                        @if ($type === 'parent')

                            <!-- Parent Information -->
                            <p class="text-xs font-bold uppercase tracking-widest text-blue-700 pt-1">
                                Parent / Guardian Information
                            </p>

                            <!-- Relationship to Student -->
                            <div>

                                <label
                                    for="relationship_to_student"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Relationship to Student
                                </label>

                                <div class="relative">

                                    <select
                                        id="relationship_to_student"
                                        name="relationship_to_student"
                                        required
                                        class="block w-full px-4 pr-10 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none appearance-none"
                                    >
                                        <option value="" disabled {{ old('relationship_to_student', $profile->relationship_to_student) ? '' : 'selected' }}>
                                            Select Relationship
                                        </option>

                                        @foreach (\App\Http\Controllers\Auth\CompleteProfileController::RELATIONSHIPS as $value => $label)

                                            <option value="{{ $value }}" {{ old('relationship_to_student', $profile->relationship_to_student) === $value ? 'selected' : '' }}>
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

                                @if ($errors->get('relationship_to_student'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('relationship_to_student') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Student Information -->
                            <p class="text-xs font-bold uppercase tracking-widest text-blue-700 pt-1">
                                Student Information
                            </p>

                            <!-- Student Full Name -->
                            <div>

                                <label
                                    for="student_full_name"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Student Full Name
                                </label>

                                <input
                                    id="student_full_name"
                                    name="student_full_name"
                                    type="text"
                                    value="{{ old('student_full_name', $profile->student_full_name) }}"
                                    required
                                    autocomplete="off"
                                    placeholder="Enter the student's full name"
                                    class="block w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                @if ($errors->get('student_full_name'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('student_full_name') }}
                                    </p>

                                @endif

                            </div>


                            <!-- Student ID -->
                            <div>

                                <label
                                    for="student_id"
                                    class="block text-sm font-semibold text-gray-800 mb-2"
                                >
                                    Student ID <span class="text-gray-400 font-normal">(optional)</span>
                                </label>

                                <input
                                    id="student_id"
                                    name="student_id"
                                    type="text"
                                    value="{{ old('student_id', $profile->student_id) }}"
                                    autocomplete="off"
                                    placeholder="e.g. 2024-12345"
                                    class="block w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                                @if ($errors->get('student_id'))

                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $errors->first('student_id') }}
                                    </p>

                                @endif

                        </div>


                        @endif


                        @if ($type !== 'student')


                        <!-- Contact Number (shared) -->
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
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                        />
                                    </svg>

                                </div>

                                <input
                                    id="contact_number"
                                    name="contact_number"
                                    type="tel"
                                    value="{{ old('contact_number', $isStudent ? $profile->contact_number : $profile->contact_number) }}"
                                    required
                                    autocomplete="tel"
                                    placeholder="e.g. 0917 123 4567"
                                    class="block w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition outline-none"
                                >

                            </div>

                            @if ($errors->get('contact_number'))

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $errors->first('contact_number') }}
                                </p>

                            @endif

                        </div>

                        @endif


                        <!-- Avatar Upload -->
                        <div class="mb-4">
                            <label
                                for="avatar"
                                class="block text-sm font-medium text-gray-800 mb-1">
                                Profile Picture
                            </label>

                            <div class="relative">
                                <input
                                    id="avatar"
                                    name="avatar"
                                    type="file"
                                    class="block w-full text-sm text-gray-500 file:border-b-2 file:border-blue-500 file:rounded-lg file:py-2 file:pl-4 file:file:text-sm file:font-medium file:text-gray-900 file:select-none rtl:file:select-rtl"
                                    required>
                                <div
                                    class="absolute -top-0 right-0 w-8 h-8 rounded-full bg-blue-500 p-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16c1.1 0 2 .9 2 2v2c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm3 5H6v14l8-2 8 2V11z"/>
                                    </svg>
                                </div>
                            </div>

                            @if ($user->avatar)
                                <div class="mt-3">
                                    <img
                                        src="{{ $user->avatar_url }}"
                                        alt="Profile picture"
                                        class="w-24 h-24 rounded-full object-cover mt-2">
                                </div>
                            @endif
                        </div>

<!-- Save Button -->
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
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 5c-2.31 0-4.456.654-6.22 1.784M12 19a9 9 0 009-9m-9 9a9 9 0 01-9-9"
                                />
                            </svg>

                            Save &amp; Continue to Dashboard

                        </button>

                    </form>


                    <!-- Back to Home -->
                    <div class="mt-7 flex justify-center">

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
</html>
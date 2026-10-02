<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        ISUFSTPASS - QR Code-Based Document Management System
    </title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap"
        rel="stylesheet"
    />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.pwa-head')
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">

    {{-- =========================================================
        NAVIGATION
    ========================================================== --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="h-20 flex items-center justify-between">

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center gap-3">

                    <img
                        src="{{ asset('img/isufstpass-logo.png') }}"
                        alt="ISUFSTPASS Logo"
                        class="w-12 h-12 object-contain"
                    >

                    <div class="leading-tight">
                        <h1 class="text-lg font-extrabold text-blue-900">
                            ISUFSTPASS
                        </h1>

                        <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide">
                            Transaction Management System
                        </p>
                    </div>

                </a>


                {{-- Desktop Navigation --}}
                <nav class="hidden md:flex items-center gap-7 text-sm font-medium">

                    <a href="#home"
                       class="text-gray-700 hover:text-blue-700 transition">
                        Home
                    </a>

                    <a href="#services"
                       class="text-gray-700 hover:text-blue-700 transition">
                        Services
                    </a>

                    <a href="#how-it-works"
                       class="text-gray-700 hover:text-blue-700 transition">
                        How It Works
                    </a>

                    <a href="#about"
                       class="text-gray-700 hover:text-blue-700 transition">
                        About
                    </a>

                </nav>

            </div>

        </div>

    </header>

{{-- =========================================================
        HERO SECTION
    ========================================================== --}}
    <section
        id="home"
        class="relative min-h-[700px] overflow-hidden bg-[#12347d]"
    >

        {{-- CAMPUS BACKGROUND IMAGE --}}
        <div class="absolute inset-0">

            <img
                src="{{ asset('images/isufst-campus.jpg') }}"
                alt="ISUFST Campus"
                class="h-full w-full object-cover"
            >

{{-- Dark blue overlay --}}
        <div class="absolute inset-0 bg-[#062b70]/55"></div>

        {{-- Subtle blue gradient --}}
        <div class="absolute inset-0 bg-gradient-to-r
                    from-[#062b70]/70
                    via-[#123f91]/50
                    to-[#123f91]/25">
        </div>

        </div>


        {{-- Decorative circles --}}
        <div class="absolute -right-32 -top-32
                    h-[500px] w-[500px]
                    rounded-full bg-blue-400/10">
        </div>

        <div class="absolute -bottom-40 -left-40
                    h-[500px] w-[500px]
                    rounded-full bg-blue-300/10">
        </div>


        {{-- HERO CONTENT --}}
        <div class="relative z-10 mx-auto
                    flex min-h-[700px]
                    max-w-7xl items-center
                    px-6 py-20
                    sm:px-10 lg:px-12">

            <div class="max-w-3xl">

                {{-- University Badge --}}
                <div class="mb-8 inline-flex items-center gap-3
                            rounded-full border border-white/25
                            bg-white/10 px-5 py-3
                            backdrop-blur-md">

                    <span class="h-2.5 w-2.5 rounded-full bg-green-400"></span>

                    <span class="text-sm font-medium text-white sm:text-base">
                        {{ $institution['name'] ?? 'Iloilo State University of Fisheries Science and Technology' }}
                    </span>

                </div>


                {{-- Title --}}
                <h1 class="text-4xl font-black leading-[1.05]
                   tracking-tight text-white
                   sm:text-5xl lg:text-6xl">

                    ISUFSTPASS

                </h1>


                {{-- Subtitle --}}
<h2 class="mt-4 max-w-3xl
                       text-3xl font-black leading-[1.08]
                       text-blue-200
                       sm:text-4xl lg:text-5xl">

                    A QR Code-Based
                    <br>
                    Transaction
                    <br>
                    Management
                    <br>
                    System

                </h2>


                {{-- Description --}}
                <p class="mt-8 max-w-2xl
                          text-lg leading-8
                          text-blue-50
                          sm:text-xl">

                    A QR Code-Based Transaction Management System designed to make
                    university services faster, easier, and more convenient for
                    students, alumni, faculty, and visitors.

                </p>


                {{-- Buttons --}}
                <div class="mt-10 flex flex-wrap gap-4">

                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center
                              rounded-xl bg-yellow-400
                              px-7 py-4
                              text-sm font-bold text-blue-950
                              shadow-lg shadow-yellow-400/20
                              transition hover:bg-yellow-300">

                        Get Started

                        <svg class="ml-2 h-5 w-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"/>

                        </svg>

                    </a>


                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center
                              rounded-xl border border-white/40
                              bg-white/10
                              px-7 py-4
                              text-sm font-bold text-white
                              backdrop-blur-md
                              transition hover:bg-white/20">

                        Sign In

                    </a>

                </div>


                {{-- Feature Badges --}}
                <div class="mt-12 flex flex-wrap gap-3">

                    <span class="rounded-full border border-white/20
                                 bg-white/10 px-4 py-2
                                 text-sm font-semibold text-white
                                 backdrop-blur-md">
                        ✓ Secure
                    </span>

                    <span class="rounded-full border border-white/20
                                 bg-white/10 px-4 py-2
                                 text-sm font-semibold text-white
                                 backdrop-blur-md">
                        ✓ Reliable
                    </span>

                    <span class="rounded-full border border-white/20
                                 bg-white/10 px-4 py-2
                                 text-sm font-semibold text-white
                                 backdrop-blur-md">
                        ✓ Efficient
                    </span>

                    <span class="rounded-full border border-white/20
                                 bg-white/10 px-4 py-2
                                 text-sm font-semibold text-white
                                 backdrop-blur-md">
                        ✓ Paperless
                    </span>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
        SERVICES
    ========================================================== --}}
    <section id="services" class="py-20 bg-white">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center max-w-2xl mx-auto">

                <p class="text-sm font-bold uppercase tracking-widest text-blue-700">
                    University Services
                </p>

                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    Everything You Need in One Portal
                </h2>

                <p class="mt-4 text-gray-500">
                    Access important university services without unnecessary
                    queues and repeated manual processes.
                </p>

            </div>


            {{-- Service Cards --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-12">


                {{-- Document Request --}}
                <a href="{{ route('login') }}"
                   class="group bg-white border border-gray-200 rounded-2xl p-7 hover:border-blue-500 hover:shadow-xl transition-all">

                    <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition">

                        <svg class="w-7 h-7 text-blue-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>

                        </svg>

                    </div>

                    <h3 class="mt-5 text-lg font-bold text-gray-900">
                        Document Requests
                    </h3>

                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Request academic and university documents such as
                        transcripts, certifications, diplomas, and other
                        official records online.
                    </p>

                    <span class="inline-flex items-center gap-2 mt-5 text-sm font-semibold text-blue-700">

                        Request a Document

                        <svg class="w-4 h-4 group-hover:translate-x-1 transition"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 5l7 7-7 7"/>

                        </svg>

                    </span>

                </a>


                {{-- BOOK APPOINTMENT --}}
                <a href="{{ route('login') }}"
                   class="group bg-white border border-gray-200 rounded-2xl p-7 hover:border-emerald-500 hover:shadow-xl transition-all">

                    <div class="w-14 h-14 rounded-xl bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-100 transition">

                        <svg class="w-7 h-7 text-emerald-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                        </svg>

                    </div>

                    <h3 class="mt-5 text-lg font-bold text-gray-900">
                        Book an Appointment
                    </h3>

                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Schedule an appointment with university offices,
                        select your preferred date and time, and avoid
                        unnecessary waiting.
                    </p>

                    <span class="inline-flex items-center gap-2 mt-5 text-sm font-semibold text-emerald-700">

                        Book Appointment

                        <svg class="w-4 h-4 group-hover:translate-x-1 transition"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 5l7 7-7 7"/>

                        </svg>

                    </span>

                </a>


                {{-- QR Verification --}}
                <a href="{{ route('login') }}"
                   class="group bg-white border border-gray-200 rounded-2xl p-7 hover:border-indigo-500 hover:shadow-xl transition-all">

                    <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition">

                        <svg class="w-7 h-7 text-indigo-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 2h2m2-2h2m-4 4h4m-4 2h2"/>

                        </svg>

                    </div>

                    <h3 class="mt-5 text-lg font-bold text-gray-900">
                        QR Code Verification
                    </h3>

                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Use your QR Pass for secure and convenient transaction
                        verification at authorized university offices.
                    </p>

                    <span class="inline-flex items-center gap-2 mt-5 text-sm font-semibold text-indigo-700">

                        Learn More

                        <svg class="w-4 h-4 group-hover:translate-x-1 transition"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 5l7 7-7 7"/>

                        </svg>

                    </span>

                </a>


                {{-- Status Tracking --}}
                <a href="{{ route('login') }}"
                   class="group bg-white border border-gray-200 rounded-2xl p-7 hover:border-purple-500 hover:shadow-xl transition-all">

                    <div class="w-14 h-14 rounded-xl bg-purple-50 flex items-center justify-center group-hover:bg-purple-100 transition">

                        <svg class="w-7 h-7 text-purple-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 17v-2a4 4 0 014-4h2m4 6v-2a4 4 0 00-4-4h-1m-5-5a3 3 0 116 0 3 3 0 01-6 0zM3 20a6 6 0 0112 0"/>

                        </svg>

                    </div>

                    <h3 class="mt-5 text-lg font-bold text-gray-900">
                        Transaction Tracking
                    </h3>

                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Monitor the progress of your document requests and
                        appointments in real time.
                    </p>

                </a>


                

            </div>

        </div>

    </section>


    {{-- =========================================================
        HOW IT WORKS
    ========================================================== --}}
    <section id="how-it-works" class="py-20 bg-gray-50">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center">

                <p class="text-sm font-bold uppercase tracking-widest text-blue-700">
                    Simple Process
                </p>

                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    How ISUFSTPASS Works
                </h2>

            </div>


            <div class="grid md:grid-cols-3 gap-8 mt-12">


                {{-- Step 1 --}}
                <div class="text-center">

                    <div class="relative inline-flex">

                        <div class="w-16 h-16 bg-blue-700 text-white rounded-2xl flex items-center justify-center text-xl font-extrabold shadow-lg">
                            01
                        </div>

                    </div>

                    <h3 class="mt-5 font-bold text-lg">
                        Create an Account
                    </h3>

                    <p class="mt-2 text-sm text-gray-500 max-w-xs mx-auto">
                        Register your university account and provide your
                        basic information.
                    </p>

                </div>


                {{-- Step 2 --}}
                <div class="text-center">

                    <div class="w-16 h-16 mx-auto bg-emerald-600 text-white rounded-2xl flex items-center justify-center text-xl font-extrabold shadow-lg">
                        02
                    </div>

                    <h3 class="mt-5 font-bold text-lg">
                        Request or Book
                    </h3>

                    <p class="mt-2 text-sm text-gray-500 max-w-xs mx-auto">
                        Submit a document request or book an appointment
                        with the appropriate university office.
                    </p>

                </div>


                {{-- Step 3 --}}
                <div class="text-center">

                    <div class="w-16 h-16 mx-auto bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-xl font-extrabold shadow-lg">
                        03
                    </div>

                    <h3 class="mt-5 font-bold text-lg">
                        Scan & Verify
                    </h3>

                    <p class="mt-2 text-sm text-gray-500 max-w-xs mx-auto">
                        Present your QR Pass for quick and secure verification
                        during your university transaction.
                    </p>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
        APPOINTMENT FEATURE
    ========================================================== --}}
    <section class="py-20 bg-white">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-gradient-to-r from-emerald-700 to-teal-800 rounded-3xl overflow-hidden">

                <div class="grid lg:grid-cols-2 items-center">

                    {{-- Text --}}
                    <div class="p-8 sm:p-12 lg:p-16 text-white">

                        <span class="inline-flex px-3 py-1 rounded-full bg-white/10 border border-white/20 text-sm font-medium">
                            New Service
                        </span>

                        <h2 class="mt-5 text-3xl sm:text-4xl font-extrabold">
                            Skip the Queue.
                            <span class="text-emerald-200">
                                Book an Appointment.
                            </span>
                        </h2>

                        <p class="mt-5 text-emerald-100 leading-relaxed">
                            Plan your visit before arriving on campus.
                            Choose the university office, select an available
                            date and time, and tell us the purpose of your visit.
                        </p>

                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 mt-7 px-6 py-3.5 bg-white text-emerald-800 font-bold rounded-xl hover:bg-emerald-50 transition">

                            Book an Appointment

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 7l5 5m0 0l-5 5m5-5H6"/>

                            </svg>

                        </a>

                    </div>


                    {{-- Appointment Visual --}}
                    <div class="p-8 lg:p-12">

                        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md mx-auto">

                            <div class="flex items-center justify-between mb-5">

                                <div>

                                    <p class="text-xs text-gray-500">
                                        Appointment
                                    </p>

                                    <p class="font-bold text-gray-900">
                                        Select Schedule
                                    </p>

                                </div>

                                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">

                                    <svg class="w-5 h-5 text-emerald-600"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                    </svg>

                                </div>

                            </div>


                            <div class="grid grid-cols-7 gap-2 text-center text-xs">

                                <span class="text-gray-400">S</span>
                                <span class="text-gray-400">M</span>
                                <span class="text-gray-400">T</span>
                                <span class="text-gray-400">W</span>
                                <span class="text-gray-400">T</span>
                                <span class="text-gray-400">F</span>
                                <span class="text-gray-400">S</span>

                                <span></span>
                                <span></span>
                                <span></span>

                                <span class="py-2">1</span>
                                <span class="py-2">2</span>
                                <span class="py-2">3</span>
                                <span class="py-2">4</span>

                                <span class="py-2">5</span>
                                <span class="py-2">6</span>

                                <span class="py-2 bg-emerald-600 text-white rounded-lg font-bold">
                                    7
                                </span>

                                <span class="py-2">8</span>
                                <span class="py-2">9</span>
                                <span class="py-2">10</span>
                                <span class="py-2">11</span>

                            </div>


                            <div class="mt-5 border-t border-gray-100 pt-5">

                                <p class="text-xs font-semibold text-gray-500 mb-3">
                                    Available Time
                                </p>

                                <div class="grid grid-cols-2 gap-2">

                                    <span class="px-3 py-2 text-xs text-center bg-emerald-50 text-emerald-700 rounded-lg font-medium">
                                        9:00 AM
                                    </span>

                                    <span class="px-3 py-2 text-xs text-center bg-gray-50 text-gray-400 rounded-lg">
                                        10:00 AM
                                    </span>

                                    <span class="px-3 py-2 text-xs text-center bg-emerald-50 text-emerald-700 rounded-lg font-medium">
                                        1:00 PM
                                    </span>

                                    <span class="px-3 py-2 text-xs text-center bg-emerald-50 text-emerald-700 rounded-lg font-medium">
                                        2:00 PM
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
        STATS
    ========================================================== --}}
    <section class="bg-blue-950 py-12">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">

                <div>
                    <p class="text-3xl font-extrabold">100%</p>
                    <p class="text-sm text-blue-200 mt-1">
                        Online Process
                    </p>
                </div>

                <div>
                    <p class="text-3xl font-extrabold">24/7</p>
                    <p class="text-sm text-blue-200 mt-1">
                        Transaction Tracking
                    </p>
                </div>

                <div>
                    <p class="text-3xl font-extrabold">QR</p>
                    <p class="text-sm text-blue-200 mt-1">
                        Secure Verification
                    </p>
                </div>

                <div>
                    <p class="text-3xl font-extrabold">Easy</p>
                    <p class="text-sm text-blue-200 mt-1">
                        Appointment Booking
                    </p>
                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
        ABOUT
    ========================================================== --}}
    <section id="about" class="py-20 bg-white">

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

            <p class="text-sm font-bold uppercase tracking-widest text-blue-700">
                About ISUFSTPASS
            </p>

            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-gray-900">
                A Better Way to Access University Services
            </h2>

            <p class="mt-6 text-gray-600 leading-relaxed">

                ISUFSTPASS is a QR Code-Based Transaction Management System
                developed to improve the delivery of university services.
                It provides a centralized platform where users can submit
                document requests, book appointments, monitor transaction
                status, and use QR codes for secure verification.

            </p>

        </div>

    </section>


    {{-- =========================================================
        CTA
    ========================================================== --}}
    <section class="py-16 bg-gray-50">

        <div class="max-w-5xl mx-auto px-4 text-center">

            <h2 class="text-3xl font-extrabold text-gray-900">
                Ready to use ISUFSTPASS?
            </h2>

            <p class="mt-3 text-gray-500">
                Start managing your university transactions online.
            </p>

            
        </div>

    </section>


    {{-- =========================================================
        FOOTER
    ========================================================== --}}
    <footer class="bg-gray-950 text-gray-400">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10">

                {{-- Branding --}}
                <div>

                    <div class="flex items-center gap-3">

                        <img
                            src="{{ asset('img/isufstpass-logo.png') }}"
                            alt="ISUFSTPASS"
                            class="w-11 h-11 object-contain"
                        >

                        <div>

                            <p class="text-white font-bold">
                                ISUFSTPASS
                            </p>

                            <p class="text-xs">
                                QR Code-Based Transaction Management System
                            </p>

                        </div>

                    </div>

                    <p class="mt-5 text-sm leading-relaxed">
                        A digital platform for convenient, secure, and
                        efficient university transactions.
                    </p>

                </div>


                {{-- Contact / Institutional Details --}}
                <div>

                    <h3 class="text-white font-semibold mb-4">
                        {{ $institution['name'] ?? 'ISUFST' }}
                    </h3>

                    <div class="space-y-3 text-sm">


                        <p>
                            {{ $institution['address'] ?? 'Iloilo, Philippines' }}
                        </p>

                        <p>
                            {{ $institution['phone'] ?? '(033) 555-1234' }}
                        </p>

                        <p>
                            {{ $institution['email'] ?? 'isufst@edu.ph' }}
                        </p>

                    </div>

                </div>

                {{-- Quick Links --}}
                <div>

                    <h3 class="text-white font-semibold mb-4">
                        Quick Links
                    </h3>

                    <div class="space-y-3 text-sm">

                        <a href="#home"
                           class="block hover:text-white transition">
                            Home
                        </a>

                        <a href="#services"
                           class="block hover:text-white transition">
                            Services
                        </a>

                        <a href="#how-it-works"
                           class="block hover:text-white transition">
                            How It Works
                        </a>

                        <a href="{{ route('login') }}"
                           class="block hover:text-white transition">
                            Sign In
                        </a>

                    </div>

                </div>


                {{-- Services --}}
                <div>

                    <h3 class="text-white font-semibold mb-4">
                        Services
                    </h3>

                    <div class="space-y-3 text-sm">

                        <p>Document Requests</p>

                        <p>Book an Appointment</p>

                        <p>QR Verification</p>

                        <p>Transaction Tracking</p>

                    </div>

                </div>

            </div>


            <div class="border-t border-gray-800 mt-10 pt-6 text-center text-xs">

                © {{ date('Y') }} ISUFSTPASS.
                All rights reserved.

            </div>

        </div>

    </footer>

</body>
</html>
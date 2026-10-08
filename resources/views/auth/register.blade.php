<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ISUFSTPASS | Create Your Account</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap"
          rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.pwa-head')
</head>

<body class="font-sans antialiased bg-slate-100 min-h-screen">

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">

        <!-- MAIN CONTAINER -->
        <div class="w-full max-w-7xl bg-white rounded-[2rem] shadow-[0_25px_70px_-20px_rgba(15,23,42,0.25)] overflow-hidden flex flex-col lg:flex-row">

            <!-- =====================================================
                 LEFT SIDE
            ====================================================== -->
            <div class="hidden lg:flex lg:w-[44%] relative overflow-hidden
                        bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800
                        text-white">

                <!-- Decorative Shapes -->
                <div class="absolute -top-32 -left-32 w-[28rem] h-[28rem]
                            rounded-full bg-blue-700/30"></div>

                <div class="absolute -bottom-40 -right-32 w-[32rem] h-[32rem]
                            rounded-full bg-blue-500/20"></div>

                <div class="absolute top-1/2 -right-20 w-56 h-56
                            rounded-full bg-yellow-400/10"></div>

                <div class="absolute bottom-20 left-20 w-20 h-20
                            rounded-full border border-yellow-300/20"></div>

                <!-- Yellow Accent -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-yellow-400"></div>

                <div class="relative z-10 flex flex-col justify-between
                            w-full p-10 xl:p-14">

                    <!-- BRAND -->
                    <div>

                        <div class="flex items-center gap-4">

                            <div class="w-[68px] h-[68px]
                                        bg-white rounded-2xl
                                        flex items-center justify-center
                                        shadow-xl shadow-black/20">

                                <img
                                    src="{{ asset('img/isufstpass-logo.png') }}"
                                    alt="ISUFSTPASS Logo"
                                    class="w-12 h-12 object-contain"
                                >

                            </div>

                            <div>
                                <p class="text-2xl font-black tracking-wide">
                                    ISUFSTPASS
                                </p>

                                <p class="text-blue-200 text-sm mt-0.5">
                                    Iloilo State University of Fisheries Science and Technology
                                </p>

                                <div class="flex items-center gap-2 mt-2">

                                    <span class="w-2 h-2 rounded-full bg-yellow-400"></span>

                                    <span class="text-[11px] uppercase tracking-[0.18em]
                                                 text-blue-200 font-semibold">
                                        Digital Transaction Portal
                                    </span>

                                </div>
                            </div>

                        </div>

                    </div>


                    <!-- MAIN MESSAGE -->
                    <div class="my-10 xl:my-14">

                        <div class="inline-flex items-center gap-2
                                    px-3.5 py-2
                                    rounded-full
                                    bg-white/10
                                    border border-white/10
                                    backdrop-blur-sm
                                    mb-6">

                            <span class="w-2 h-2 rounded-full bg-yellow-400"></span>

                            <span class="text-xs font-bold uppercase
                                         tracking-[0.15em] text-blue-100">
                                University Transaction Portal
                            </span>

                        </div>

                        <h1 class="text-4xl xl:text-[3.4rem]
                                   font-black leading-[1.05]
                                   tracking-tight">

                            One Account.
                            
                            <span class="block mt-2 text-yellow-400">
                                All Transactions.
                            </span>

                        </h1>

                        <p class="mt-7 text-blue-100
                                  text-base xl:text-lg
                                  leading-8 max-w-md">

                            Create your ISUFSTPASS account and experience
                            a faster, more convenient, organized, and secure
                            way to manage your university transactions.

                        </p>

                    </div>


                    <!-- FEATURES -->
                    <div>

                        <div class="grid grid-cols-3 gap-3">

                            <!-- Documents -->
                            <div class="group rounded-2xl
                                        bg-white/[0.08]
                                        border border-white/10
                                        backdrop-blur-md
                                        p-4
                                        hover:bg-white/[0.13]
                                        transition-all duration-300">

                                <div class="w-10 h-10 rounded-xl
                                            bg-blue-500/20
                                            flex items-center justify-center
                                            mb-3">

                                    <svg class="w-5 h-5 text-yellow-300"
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

                                <p class="text-sm font-bold">
                                    Documents
                                </p>

                                <p class="text-[11px] text-blue-200 mt-1">
                                    Request online
                                </p>

                            </div>


                            <!-- Appointments -->
                            <div class="group rounded-2xl
                                        bg-white/[0.08]
                                        border border-white/10
                                        backdrop-blur-md
                                        p-4
                                        hover:bg-white/[0.13]
                                        transition-all duration-300">

                                <div class="w-10 h-10 rounded-xl
                                            bg-blue-500/20
                                            flex items-center justify-center
                                            mb-3">

                                    <svg class="w-5 h-5 text-yellow-300"
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

                                <p class="text-sm font-bold">
                                    Appointments
                                </p>

                                <p class="text-[11px] text-blue-200 mt-1">
                                    Book easily
                                </p>

                            </div>


                            <!-- QR -->
                            <div class="group rounded-2xl
                                        bg-white/[0.08]
                                        border border-white/10
                                        backdrop-blur-md
                                        p-4
                                        hover:bg-white/[0.13]
                                        transition-all duration-300">

                                <div class="w-10 h-10 rounded-xl
                                            bg-blue-500/20
                                            flex items-center justify-center
                                            mb-3">

                                    <svg class="w-5 h-5 text-yellow-300"
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

                                <p class="text-sm font-bold">
                                    QR Enabled
                                </p>

                                <p class="text-[11px] text-blue-200 mt-1">
                                    Fast & secure
                                </p>

                            </div>

                        </div>

                        <div class="flex items-center gap-2 mt-7">

                            <div class="h-px flex-1 bg-white/10"></div>

                            <span class="text-[10px] text-blue-300 uppercase
                                         tracking-[0.2em]">
                                Secure • Reliable • Efficient
                            </span>

                            <div class="h-px flex-1 bg-white/10"></div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 RIGHT SIDE
            ====================================================== -->
            <div class="w-full lg:w-[56%]
                        flex items-center justify-center
                        bg-white
                        px-5 py-8
                        sm:px-10 sm:py-12
                        lg:px-12 xl:px-16">

                <div class="w-full max-w-2xl">

                    <!-- TOP BAR -->
                    <div class="flex items-center justify-between mb-8">

                        <a href="{{ url('/') }}"
                           class="flex items-center gap-3 group">

                            <div class="w-11 h-11 rounded-xl
                                        bg-blue-50
                                        flex items-center justify-center
                                        border border-blue-100
                                        group-hover:bg-blue-100
                                        transition">

                                <img
                                    src="{{ asset('img/isufstpass-logo.png') }}"
                                    alt="ISUFSTPASS"
                                    class="w-8 h-8 object-contain"
                                >

                            </div>

                            <div class="hidden sm:block">

                                <p class="font-extrabold text-gray-900 leading-none">
                                    ISUFSTPASS
                                </p>

                                <p class="text-[10px] uppercase tracking-wider
                                          text-gray-400 mt-1">
                                    Transaction Portal
                                </p>

                            </div>

                        </a>


                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2
                                  px-4 py-2.5
                                  rounded-xl
                                  border border-gray-200
                                  text-sm font-bold text-gray-600
                                  hover:border-blue-200
                                  hover:text-blue-700
                                  hover:bg-blue-50
                                  transition">

                            <span>Sign In</span>

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                />

                            </svg>

                        </a>

                    </div>


                    <!-- MOBILE LOGO -->
                    <div class="lg:hidden flex justify-center mb-7">

                        <div class="w-20 h-20 rounded-3xl
                                    bg-blue-50
                                    border border-blue-100
                                    flex items-center justify-center
                                    shadow-sm">

                            <img
                                src="{{ asset('img/isufstpass-logo.png') }}"
                                alt="ISUFSTPASS"
                                class="w-14 h-14 object-contain"
                            >

                        </div>

                    </div>


                    <!-- HEADING -->
                    <div class="mb-8">

                        <div class="flex items-center gap-2 mb-3">

                            <span class="w-8 h-1 rounded-full bg-yellow-400"></span>

                            <span class="text-xs font-bold
                                         uppercase tracking-[0.18em]
                                         text-blue-600">
                                Get Started
                            </span>

                        </div>

                        <h2 class="text-3xl sm:text-4xl
                                   font-black text-gray-950
                                   tracking-tight">

                            Create Your Account

                        </h2>

                        <p class="mt-3 text-gray-500 text-sm sm:text-base
                                  leading-6 max-w-lg">

                            Choose the account type that best describes you
                            to continue with your ISUFSTPASS registration.

                        </p>

                    </div>


                    <!-- ACCOUNT TYPE CARDS -->
                    <div class="grid sm:grid-cols-2 gap-4">


                        <!-- =================================================
                             STUDENT
                        ================================================== -->
                        <a
                            href="{{ route('register.form', ['type' => 'student']) }}"
                            class="group relative flex flex-col
                                   p-6 rounded-2xl
                                   border border-blue-100
                                   bg-gradient-to-br from-blue-50 to-white
                                   hover:border-blue-500
                                   hover:shadow-xl hover:shadow-blue-900/10
                                   hover:-translate-y-1
                                   transition-all duration-300">

                            <!-- Top Accent -->
                            <div class="absolute top-0 left-6 right-6
                                        h-1 rounded-b-full
                                        bg-blue-600
                                        opacity-0
                                        group-hover:opacity-100
                                        transition"></div>

                            <div class="flex items-start justify-between">

                                <div class="w-14 h-14 rounded-2xl
                                            bg-blue-600
                                            flex items-center justify-center
                                            shadow-lg shadow-blue-600/20
                                            group-hover:scale-105
                                            transition-transform">

                                    <svg class="w-7 h-7 text-white"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M12 14l9-5-9-5-9 5 9 5z"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M12 14v7"
                                        />

                                    </svg>

                                </div>

                                <span class="w-8 h-8 rounded-full
                                             bg-white
                                             border border-blue-100
                                             flex items-center justify-center
                                             text-blue-500
                                             group-hover:bg-blue-600
                                             group-hover:text-white
                                             group-hover:border-blue-600
                                             transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />

                                    </svg>

                                </span>

                            </div>

                            <div class="mt-5">

                                <p class="text-lg font-black text-gray-900">
                                    Student
                                </p>

                                <p class="mt-1 text-sm text-gray-500 leading-5">
                                    For currently enrolled ISUFST students.
                                </p>

                            </div>

                            <div class="mt-5 pt-4 border-t border-blue-100
                                        flex items-center justify-between">

                                <span class="text-xs font-bold text-blue-600">
                                    Student Account
                                </span>

                                <span class="text-xs font-semibold text-gray-400
                                             group-hover:text-blue-600 transition">
                                    Register →
                                </span>

                            </div>

                        </a>


                        <!-- =================================================
                             OFFICE / STAFF
                        ================================================== -->
                        <a
                            href="{{ route('register.form', ['type' => 'office']) }}"
                            class="group relative flex flex-col
                                   p-6 rounded-2xl
                                   border border-indigo-100
                                   bg-gradient-to-br from-indigo-50 to-white
                                   hover:border-indigo-500
                                   hover:shadow-xl hover:shadow-indigo-900/10
                                   hover:-translate-y-1
                                   transition-all duration-300">

                            <div class="absolute top-0 left-6 right-6
                                        h-1 rounded-b-full
                                        bg-indigo-600
                                        opacity-0
                                        group-hover:opacity-100
                                        transition"></div>

                            <div class="flex items-start justify-between">

                                <div class="w-14 h-14 rounded-2xl
                                            bg-indigo-600
                                            flex items-center justify-center
                                            shadow-lg shadow-indigo-600/20
                                            group-hover:scale-105
                                            transition-transform">

                                    <svg class="w-7 h-7 text-white"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 10h.01M15 10h.01M9 13h.01M15 13h.01"
                                        />

                                    </svg>

                                </div>

                                <span class="w-8 h-8 rounded-full
                                             bg-white
                                             border border-indigo-100
                                             flex items-center justify-center
                                             text-indigo-500
                                             group-hover:bg-indigo-600
                                             group-hover:text-white
                                             group-hover:border-indigo-600
                                             transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />

                                    </svg>

                                </span>

                            </div>

                            <div class="mt-5">

                                <p class="text-lg font-black text-gray-900">
                                    Office / Staff
                                </p>

                                <p class="mt-1 text-sm text-gray-500 leading-5">
                                    For university personnel and office staff.
                                </p>

                            </div>

                            <div class="mt-5 pt-4 border-t border-indigo-100
                                        flex items-center justify-between">

                                <span class="text-xs font-bold text-indigo-600">
                                    Staff Account
                                </span>

                                <span class="text-xs font-semibold text-gray-400
                                             group-hover:text-indigo-600 transition">
                                    Register →
                                </span>

                            </div>

                        </a>


                        <!-- =================================================
                             OTHER
                        ================================================== -->
                        <a
                            href="{{ route('register.form', ['type' => 'other']) }}"
                            class="group relative sm:col-span-2
                                   flex items-center gap-5
                                   p-6 rounded-2xl
                                   border border-yellow-200
                                   bg-gradient-to-r from-yellow-50 via-white to-white
                                   hover:border-yellow-400
                                   hover:shadow-xl hover:shadow-yellow-900/10
                                   hover:-translate-y-1
                                   transition-all duration-300">

                            <div class="absolute top-0 left-6 right-6
                                        h-1 rounded-b-full
                                        bg-yellow-400
                                        opacity-0
                                        group-hover:opacity-100
                                        transition"></div>

                            <div class="w-14 h-14 shrink-0
                                        rounded-2xl
                                        bg-yellow-400
                                        flex items-center justify-center
                                        shadow-lg shadow-yellow-400/20
                                        group-hover:scale-105
                                        transition-transform">

                                <svg class="w-7 h-7 text-blue-950"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />

                                </svg>

                            </div>

                            <div class="flex-1 min-w-0">

                                <div class="flex items-center gap-2">

                                    <p class="text-lg font-black text-gray-900">
                                        Other
                                    </p>

                                    <span class="px-2 py-1 rounded-md
                                                 bg-yellow-100
                                                 text-yellow-700
                                                 text-[10px]
                                                 font-bold uppercase">
                                        Guest Access
                                    </span>

                                </div>

                                <p class="mt-1 text-sm text-gray-500">
                                    For alumni, guests, parents, and guardians.
                                </p>

                            </div>

                            <div class="hidden sm:flex
                                        w-10 h-10 rounded-full
                                        bg-white
                                        border border-yellow-200
                                        items-center justify-center
                                        text-yellow-600
                                        group-hover:bg-yellow-400
                                        group-hover:text-blue-950
                                        group-hover:border-yellow-400
                                        transition">

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />

                                </svg>

                            </div>

                        </a>

                    </div>


                    <!-- LOGIN -->
                    <div class="mt-8 text-center">

                        <p class="text-sm text-gray-500">

                            Already have an account?

                            <a
                                href="{{ route('login') }}"
                                class="font-extrabold text-blue-600
                                       hover:text-blue-800
                                       transition">

                                Sign In

                            </a>

                        </p>

                    </div>


                    <!-- BACK HOME -->
                    <div class="mt-5 flex justify-center">

                        <a
                            href="{{ url('/') }}"
                            class="inline-flex items-center gap-2
                                   text-xs font-bold
                                   text-gray-400
                                   hover:text-blue-600
                                   transition">

                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

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


                    <!-- FOOTER -->
                    <div class="mt-7 pt-5
                                border-t border-gray-100
                                text-center">

                        <p class="text-[11px] text-gray-400">
                            © {{ date('Y') }} ISUFSTPASS
                        </p>

                        <p class="text-[10px] text-gray-400 mt-1">
                            QR Code-Based Transaction Management System
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>
</html>

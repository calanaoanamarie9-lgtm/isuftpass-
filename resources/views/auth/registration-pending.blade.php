<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ISUFSTPASS | Application Submitted</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.pwa-head')
</head>

<body class="font-sans antialiased bg-slate-100 min-h-screen">

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">

        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden">

            <!-- Accent bar -->
            <div class="h-1.5 w-full bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-600"></div>

            <div class="px-6 py-12 sm:px-12 sm:py-14 text-center">

                <!-- Clock icon -->
                <div class="w-20 h-20 mx-auto rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-10 h-10"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h1 class="mt-7 text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                    Application Submitted
                </h1>

                <p class="mt-4 text-gray-500 leading-7 max-w-md mx-auto">
                    Your office account is now pending review. An administrator has to
                    approve it before you can sign in to ISUFSTPASS.
                </p>

                <!-- Notice -->
                <div class="mt-8 text-left rounded-2xl border border-indigo-100 bg-indigo-50/60 p-6">
                    <p class="text-sm font-bold text-indigo-900">
                        What happens next
                    </p>

                    <ul class="mt-4 space-y-3 text-sm text-indigo-900/80">
                        <li class="flex gap-3">
                            <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[11px] font-bold">1</span>
                            The administrator reviews your office and staff details.
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[11px] font-bold">2</span>
                            Once approved, sign in with the email and password you just created.
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[11px] font-bold">3</span>
                            If the application is declined, the reason will show on your sign-in attempt.
                        </li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="mt-9 flex flex-col sm:flex-row items-center justify-center gap-3">

                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
                        Go to Sign In
                    </a>

                    <a href="{{ url('/') }}"
                       class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-white border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-50 transition">
                        Back to Home
                    </a>

                </div>

                <p class="mt-8 text-[11px] text-gray-400">
                    © {{ date('Y') }} ISUFSTPASS
                </p>

            </div>

        </div>

    </div>


    {{-- ================= SWEETALERT ================= --}}
    {{-- The popup is the message; the card behind it is the fallback for
         a browser that never ran the script. Confirming takes the
         applicant back to the Registration Page, as promised. --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            if (window.Swal) {

                window.Swal.fire({

                    icon: 'info',

                    title: 'Registration Submitted!',

                    text: 'Your registration is pending administrator approval. You will receive an email notification once your account is approved, after which you may log in to ISUFSTPASS.',

                    confirmButtonText: 'OK',

                    confirmButtonColor: '#123b78',

                    allowOutsideClick: false,

                    allowEscapeKey: false,

                }).then(function () {

                    window.location.href = '{{ route('register') }}';

                });

            }

        });

    </script>

</body>
</html>

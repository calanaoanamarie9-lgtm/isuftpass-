<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f2a75">
    <title>Invalid QR Code — ISUFSTPASS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans">

    {{-- Same masthead as every other scan result, so a rejected code still
         reads as an official ISUFSTPASS page rather than a broken link. --}}
    <header class="bg-blue-950 text-white shadow-lg">
        <div class="max-w-4xl mx-auto px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white rounded-xl p-1 flex items-center justify-center">
                    <img
                        src="{{ asset('img/isufstpass-logo.png') }}"
                        alt="ISUFSTPASS"
                        class="w-full h-full object-contain"
                    >
                </div>
                <div>
                    <h1 class="font-extrabold text-lg tracking-wide">ISUFSTPASS</h1>
                    <p class="text-xs text-blue-200">Digital Campus Transaction System</p>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 sm:py-10">

        <div class="max-w-sm mx-auto bg-white rounded-3xl shadow-xl overflow-hidden border border-rose-200">

            <div class="px-6 py-8 text-center text-white bg-gradient-to-r from-rose-600 to-red-600">
                <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">
                    <svg class="w-12 h-12 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                <h1 class="mt-5 text-2xl font-extrabold">Invalid QR Code</h1>
                <p class="mt-2 text-sm text-red-100">{{ $message }}</p>
            </div>

            <div class="p-6 text-center">
                <p class="text-sm text-gray-600 leading-6">
                    This QR code was not issued by ISUFSTPASS, or it is no longer
                    active. Please ask the office that gave it to you for a new one.
                </p>
            </div>

        </div>

        <div class="text-center mt-6">
            <p class="text-xs font-bold text-blue-900">ISUFSTPASS</p>
            <p class="text-[10px] text-gray-400 mt-1">QR Code-Based Transaction Management System</p>
        </div>

    </main>

</body>
</html>

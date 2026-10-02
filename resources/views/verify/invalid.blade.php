<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invalid QR Code — ISUFSTPASS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-10 px-4">
    <div class="w-full max-w-sm">
        <div class="bg-white rounded-3xl border border-red-200 shadow-xl overflow-hidden">
            <div class="px-6 py-10 text-center">
                <p class="text-5xl">❌</p>
                <h1 class="mt-3 text-lg font-extrabold text-gray-900">Invalid QR Code</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $message }}</p>
            </div>
        </div>
        <p class="mt-4 text-center text-xs text-gray-400 font-semibold tracking-wide">ISUFSTPASS</p>
    </div>
</body>
</html>
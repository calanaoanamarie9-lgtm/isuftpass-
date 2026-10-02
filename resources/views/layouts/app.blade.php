<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ISUFSTPASS - QR Code-Based Document Management System</title>

        @include('partials.pwa-head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ open: false }" class="min-h-screen bg-gray-100">
            @include('layouts.sidebar')

            {{-- Mobile Overlay --}}
            <div x-show="open"
                 x-cloak
                 @click="open = false"
                 class="fixed inset-0 z-30 bg-black/50 lg:hidden">
            </div>

            <div class="lg:pl-64 flex flex-col min-h-screen">

                {{-- Mobile Top Bar --}}
                <div class="lg:hidden sticky top-0 z-20 bg-[#12347d] text-white h-16 flex items-center justify-between px-4">
                    <div class="flex items-center gap-3">
                        <button type="button" data-no-confirm @click="open = true" class="p-2 -ml-2 rounded-md hover:bg-white/10 transition">
                            <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('img/isufstpass-logo.png') }}" alt="ISUFSTPASS" class="w-8 h-8 object-contain">
                            <span class="font-extrabold text-sm">ISUFSTPASS</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 rounded-md hover:bg-white/10 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
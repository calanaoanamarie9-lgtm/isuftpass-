<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - ISUFSTPASS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.pwa-head')
</head>

<body class="min-h-screen bg-slate-50">
<div class="min-h-screen flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-md">

        {{-- LOGO --}}
        <div class="text-center mb-8">
            <div class="mx-auto flex h-20 w-20 items-center justify-center
                        rounded-2xl bg-blue-700 shadow-lg shadow-blue-200">
                <span class="text-2xl font-black text-white">
                    IP
                </span>
            </div>
            <h1 class="mt-5 text-2xl font-extrabold text-slate-900">
                ISUFSTPASS
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                QR Code-Based Transaction Management System
            </p>
        </div>

        {{-- CARD --}}
        <div class="rounded-3xl border border-slate-200 bg-white
                    p-7 shadow-xl shadow-slate-200/50">

            {{-- ICON --}}
            <div class="mx-auto flex h-16 w-16 items-center justify-center
                        rounded-2xl bg-blue-50 text-blue-600">
                <svg
                    class="h-8 w-8"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                </svg>
            </div>

            <div class="mt-6 text-center">
                <h2 class="text-xl font-extrabold text-slate-900">
                    Verify Your Email
                </h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Thanks for creating an ISUFSTPASS account!
                    Before continuing, please verify your email address
                    by clicking the verification link we sent to:
                </p>

                <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                    <p class="break-all text-sm font-bold text-blue-700">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </div>

            {{-- SUCCESS MESSAGE --}}
            @if (session('status') === 'verification-link-sent')
                <div class="mt-5 rounded-xl border border-green-200
                            bg-green-50 p-4">
                    <div class="flex gap-3">
                        <svg
                            class="h-5 w-5 shrink-0 text-green-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        <p class="text-sm font-medium text-green-700">
                            A new verification link has been sent to your email.
                        </p>
                    </div>
                </div>
            @endif

            {{-- FAILURE MESSAGE --}}
            @if (session('status') === 'verification-link-failed')
                <div class="mt-5 rounded-xl border border-amber-200
                            bg-amber-50 p-4">
                    <div class="flex gap-3">
                        <svg
                            class="h-5 w-5 shrink-0 text-amber-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                            />
                        </svg>
                        <p class="text-sm font-medium text-amber-800">
                            We couldn't send it just now. Please wait a moment
                            and press the button again.
                        </p>
                    </div>
                </div>
            @endif

            {{-- RESEND --}}
            <form
                method="POST"
                action="{{ route('verification.send') }}"
                class="mt-6"
            >
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-xl bg-blue-700 px-5 py-3.5
                           text-sm font-bold text-white shadow-lg
                           shadow-blue-200 transition
                           hover:bg-blue-800"
                >
                    Resend Verification Email
                </button>
            </form>

            {{-- LOGOUT --}}
            <form
                method="POST"
                action="{{ route('logout') }}"
                class="mt-3"
            >
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-xl border border-slate-200
                           bg-white px-5 py-3.5 text-sm font-bold
                           text-slate-600 transition hover:bg-slate-50"
                >
                    Sign Out
                </button>
            </form>

            <div class="mt-7 border-t border-slate-100 pt-5 text-center">
                <p class="text-xs leading-5 text-slate-400">
                    Didn't receive the email?
                    Check your <strong>Spam</strong> or
                    <strong>Junk</strong> folder.
                </p>
            </div>
        </div>

        {{-- FOOTER --}}
        <p class="mt-6 text-center text-xs text-slate-400">
            © {{ date('Y') }} ISUFSTPASS
        </p>

    </div>
</div>
</body>
</html>

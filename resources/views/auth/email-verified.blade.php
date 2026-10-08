<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - ISUFSTPASS</title>
    {{-- No click needed: send fresh signups to the details form, everyone
         else to the dashboard (the controller already picked which). --}}
    <meta http-equiv="refresh" content="2;url={{ $destination }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.pwa-head')
</head>

<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">

    <div class="w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-xl">

        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-50">
            <svg class="h-8 w-8 text-green-600"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="mt-5 text-2xl font-extrabold text-slate-900">
            Email Verified!
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Your ISUFSTPASS email address has been successfully verified.
        </p>

        <p class="mt-4 flex items-center justify-center gap-2 text-xs font-semibold text-slate-400">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            Redirecting you automatically…
        </p>

        <a href="{{ $destination }}"
           class="mt-6 inline-flex w-full items-center justify-center
                  rounded-xl bg-blue-700 px-6 py-3
                  text-sm font-bold text-white
                  transition hover:bg-blue-800">
            {{ $label }}
        </a>

    </div>

</body>

</html>

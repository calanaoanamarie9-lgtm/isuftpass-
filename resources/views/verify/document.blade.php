<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f2a75">
    <title>Claim Verification — ISUFSTPASS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans">

    {{-- Same masthead as verify.pass and verify.appointment: every page a QR
         can open must look like it came out of one system, not three. --}}
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

    @php
        $status = \App\Enums\DocumentRequestStatus::tryFrom($documentRequest->status);
        $ready = $documentRequest->status === 'ready_for_pickup';
        $cancelled = $documentRequest->status === 'cancelled';
        $done = $documentRequest->status === 'completed';

        // The headline colour is the state of the request: green once the
        // counter can hand the document over, red once it is void, blue while
        // it is still in the pipeline.
        $heroClass = $ready || $done
            ? 'bg-gradient-to-r from-green-600 to-emerald-600'
            : ($cancelled
                ? 'bg-gradient-to-r from-rose-600 to-red-600'
                : 'bg-gradient-to-r from-blue-900 to-blue-800');

        $heroTone = $ready || $done ? 'text-green-100' : ($cancelled ? 'text-red-100' : 'text-blue-200');
    @endphp

    <main class="max-w-4xl mx-auto px-4 py-6 sm:py-10">

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-200">

            {{-- Status hero --}}
            <div class="px-6 py-8 text-center text-white {{ $heroClass }}">
                <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">
                    @if ($ready || $done)
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    @elseif ($cancelled)
                        <svg class="w-12 h-12 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    @else
                        <svg class="w-12 h-12 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>

                <h2 class="mt-5 text-2xl sm:text-3xl font-extrabold">Document Claim Slip</h2>

                <p class="mt-2 text-sm {{ $heroTone }}">
                    {{ $status?->label() ?? 'Under Review' }} &middot; QR verified
                </p>
            </div>

            {{-- Counter band: the three facts a staff member reads first --}}
            <div class="bg-blue-950 px-5 sm:px-8 py-4 text-white">
                <div class="flex items-center justify-between gap-x-3 gap-y-2 flex-wrap">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider
                            {{ $documentRequest->isPaid() ? 'bg-green-400 text-green-950' : 'bg-amber-400 text-amber-950' }}">
                            {{ $documentRequest->isPaid() ? 'Paid' : 'Unpaid' }}
                        </span>

                        <span class="text-sm text-blue-200 break-all">
                            {{ $documentRequest->request_number }}
                        </span>
                    </div>

                    <p class="text-xs text-blue-300">
                        Filed {{ $documentRequest->created_at->format('M j, Y') }}
                    </p>
                </div>
            </div>

            <div class="p-6 sm:p-8">

                {{-- Who and how --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">Student</p>
                        <p class="mt-1 font-bold text-gray-900 break-words">{{ $documentRequest->student_name }}</p>
                        @if ($documentRequest->user?->studentProfile?->student_id)
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ $documentRequest->user->studentProfile->student_id }}
                            </p>
                        @endif
                    </div>

                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">Claim Mode</p>
                        <p class="mt-1 font-bold text-gray-900 break-words capitalize">
                            {{ str_replace('_', ' ', $documentRequest->claim_mode ?? 'personal') }}
                        </p>
                    </div>

                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">Status</p>
                        <p class="mt-1 font-bold {{ $cancelled ? 'text-rose-600' : ($ready || $done ? 'text-green-600' : 'text-blue-700') }}">
                            {{ $status?->label() ?? 'Under Review' }}
                        </p>
                    </div>

                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">Amount</p>
                        <p class="mt-1 font-bold text-gray-900">
                            &#8369;{{ number_format($documentRequest->totalFee(), 2) }}
                        </p>
                    </div>

                </div>

                {{-- Documents requested --}}
                <h3 class="mt-7 text-[11px] font-bold uppercase tracking-widest text-gray-400">Documents</h3>

                <div class="mt-2 divide-y divide-gray-100 rounded-2xl border border-gray-100 overflow-hidden">
                    @foreach ($documentRequest->documents as $doc)
                        <div class="flex items-center justify-between gap-3 px-4 py-3 bg-white">
                            <span class="text-sm font-semibold text-gray-800 break-words">{{ $doc->name }}</span>
                            <span class="text-sm font-bold text-gray-600 shrink-0">
                                &#8369;{{ number_format($doc->fee, 2) }}
                            </span>
                        </div>
                    @endforeach

                    @if ($documentRequest->others_specification)
                        <div class="flex items-center justify-between gap-3 px-4 py-3 bg-white">
                            <span class="text-sm font-semibold text-gray-800 break-words">
                                Others: {{ $documentRequest->others_specification }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-3 px-4 py-3 bg-blue-50/60">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Total</span>
                        <span class="text-sm font-extrabold text-blue-900">
                            &#8369;{{ number_format($documentRequest->totalFee(), 2) }}
                        </span>
                    </div>
                </div>

                {{-- What happens next --}}
                @if ($ready)
                    <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 p-5 text-center">
                        <p class="text-sm font-bold text-green-700">Ready for release</p>
                        <p class="text-xs text-green-600 mt-1">
                            Present this page together with a valid ID at the Registrar's counter.
                        </p>
                    </div>
                @elseif ($cancelled)
                    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 text-center">
                        <p class="text-sm font-bold text-rose-700">This claim slip has been cancelled.</p>
                        <p class="text-xs text-rose-600 mt-1">
                            Contact the office that processed this request.
                        </p>
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-5 text-center">
                        <p class="text-xs text-gray-500">
                            This claim slip is not yet ready for release. Check back later.
                        </p>
                    </div>
                @endif

                {{-- Verification footer --}}
                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400">Verified on</p>
                    <p class="mt-1 text-sm font-bold text-gray-700">{{ now()->format('F j, Y • g:i A') }}</p>

                    <p class="mt-4 text-[11px] text-gray-400 leading-5">
                        This verification page is generated by the
                        ISUFSTPASS Digital Campus Transaction System.
                    </p>

                    <p class="mt-2 text-[11px] font-semibold text-gray-400">
                        Iloilo State University of Fisheries Science and Technology
                    </p>
                </div>

            </div>

        </div>

        <div class="text-center mt-6">
            <p class="text-xs font-bold text-blue-900">ISUFSTPASS</p>
            <p class="text-[10px] text-gray-400 mt-1">QR Code-Based Transaction Management System</p>
        </div>

    </main>

</body>
</html>

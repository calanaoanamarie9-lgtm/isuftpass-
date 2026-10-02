<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Claim Verification — ISUFSTPASS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-10 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl border shadow-xl overflow-hidden
            {{ $documentRequest->status === 'ready_for_pickup' ? 'border-green-200' : 'border-blue-200' }}">
            <div class="px-6 py-5 text-center
                {{ $documentRequest->status === 'ready_for_pickup' ? 'bg-green-600' : 'bg-blue-800' }}">
                <p class="text-4xl">{{ $documentRequest->status === 'ready_for_pickup' ? '📦' : '📄' }}</p>
                <h1 class="mt-1 text-lg font-extrabold text-white uppercase tracking-wide">Document Claim Slip</h1>
                <p class="text-xs font-semibold mt-0.5 {{ $documentRequest->status === 'ready_for_pickup' ? 'text-green-100' : 'text-blue-100' }}">
                    Transaction No.: {{ $documentRequest->request_number }}
                </p>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-between gap-2 rounded-xl bg-blue-50/60 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Status</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                        @if ($documentRequest->status === 'cancelled') bg-red-50 text-red-600 ring-1 ring-red-200
                        @elseif ($documentRequest->status === 'completed') bg-blue-50 text-blue-700 ring-1 ring-blue-200
                        @elseif ($documentRequest->status === 'ready_for_pickup') bg-green-50 text-green-700 ring-1 ring-green-200
                        @else bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200 @endif">
                        {{ \App\Enums\DocumentRequestStatus::tryFrom($documentRequest->status)?->label() }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-blue-50/60 p-3.5">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Student</p>
                        <p class="mt-0.5 text-sm font-semibold text-gray-800">{{ $documentRequest->student_name }}</p>
                    </div>
                    <div class="rounded-xl bg-blue-50/60 p-3.5">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Claim Mode</p>
                        <p class="mt-0.5 text-sm font-semibold text-gray-800 capitalize">
                            {{ str_replace('_', ' ', $documentRequest->claim_mode ?? 'personal') }}
                        </p>
                    </div>
                </div>

                <h3 class="mt-5 text-[11px] font-bold uppercase tracking-widest text-gray-400">Documents</h3>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($documentRequest->documents as $doc)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-xs font-semibold text-blue-700">
                            {{ $doc->name }} <span class="text-blue-400">&middot; ₱{{ number_format($doc->fee, 2) }}</span>
                        </span>
                    @endforeach
                    @if ($documentRequest->others_specification)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-50 text-xs font-semibold text-indigo-700">
                            Others: {{ $documentRequest->others_specification }}
                        </span>
                    @endif
                </div>

                @if ($documentRequest->status === 'ready_for_pickup')
                    <div class="mt-5 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-center">
                        <p class="text-sm font-bold text-green-700">Ready for release</p>
                        <p class="text-xs text-green-600 mt-0.5">Present this page together with a valid ID at the Registrar's counter.</p>
                    </div>
                @else
                    <div class="mt-5 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-center">
                        <p class="text-xs text-gray-500">This claim slip is not yet ready for release. Check back later.</p>
                    </div>
                @endif
            </div>
        </div>

        <p class="mt-4 text-center text-xs text-gray-400 font-semibold tracking-wide">ISUFSTPASS</p>
    </div>
</body>
</html>
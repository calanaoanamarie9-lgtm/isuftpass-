@php
    $steps = match ($request->status) {
        'submitted' => [
            'Wait for the Registrar\'s Office to review and approve your request.',
            'Once approved, proceed to the Cashier to settle your payment.',
            'Bring your Request Number (' . $request->request_number . ') together with your official receipt when claiming.',
            'Claim your document once the status becomes Ready for Pick-up.',
        ],
        'for_signature' => [
            'Your request has been approved by the Registrar\'s Office.',
            'Proceed to the Cashier to settle your payment for the requested document(s).',
            'Bring your Request Number (' . $request->request_number . ') together with your official receipt when claiming.',
        ],
        'processing' => [
            'Your payment has been recorded by the Cashier.',
            'The Registrar\'s Office is now preparing your document(s) for release.',
            'You will receive a notification once your document is ready for pick-up.',
        ],
        'ready_for_pickup' => [
            'Proceed to the Registrar\'s Office to claim your document(s).',
            'Present your Digital Claim Pass QR code together with your official receipt or a valid ID.',
        ],
        default => [],
    };
@endphp

@if (count($steps))
    <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-5 sm:p-6">

        <div class="flex items-start gap-3">

            <div class="w-10 h-10 shrink-0 rounded-xl bg-blue-800 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>

            <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-extrabold text-[#123b78]">
                    What should I do next?
                </h3>
                <p class="mt-0.5 text-xs sm:text-sm text-blue-800/80">
                    Follow these steps to complete your request.
                </p>
            </div>

        </div>

        <ol class="mt-4 space-y-2.5">
            @foreach ($steps as $index => $step)
                <li class="flex items-start gap-3 text-sm text-blue-900">
                    <span class="mt-0.5 inline-flex w-6 h-6 shrink-0 items-center justify-center rounded-full bg-blue-800 text-white text-xs font-black">
                        {{ $index + 1 }}
                    </span>
                    <span class="leading-relaxed">{{ $step }}</span>
                </li>
            @endforeach
        </ol>

    </div>
@endif
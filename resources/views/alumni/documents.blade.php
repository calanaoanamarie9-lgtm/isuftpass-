<x-app-layout>
    @php
        $user = Auth::user();

        // Same badge colors the student list uses, so an alumni requester
        // reads the pipeline exactly the way a current student does.
        $label = fn (string $status): string => \App\Enums\DocumentRequestStatus::tryFrom($status)?->label()
            ?? ucwords(str_replace('_', ' ', $status));

        $badge = fn (string $status): array => match ($status) {
            'ready_for_pickup' => ['label' => $label($status), 'class' => 'bg-green-50 text-green-700 border border-green-200'],
            'awaiting_or_validation' => ['label' => 'Paid', 'class' => 'bg-amber-50 text-amber-700 border border-amber-200'],
            'processing', 'for_signature', 'completed' => ['label' => $label($status), 'class' => 'bg-blue-50 text-blue-700 border border-blue-200'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-red-50 text-red-600 border border-red-200'],
            default => ['label' => $label($status), 'class' => 'bg-red-50 text-red-700 border border-red-200'],
        };
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-gray-900">Document Requests</h1>
                <p class="mt-2 text-sm text-gray-500">Request transcript, diploma, certificate of graduation, and other alumni documents.</p>
                <div class="mt-6">
                    <a href="{{ route('alumni.documents.new') }}"
                       class="inline-flex items-center justify-center px-6 py-3 bg-yellow-400 text-blue-950 font-bold rounded-xl hover:bg-yellow-300 transition shadow-lg">
                        New Request
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900">My Requests</h2>
                    <span class="text-xs text-gray-400">Requests are reviewed by the Registrar</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($documents ?? [] as $request)
                        @php $status = $badge((string) $request->status); @endphp

                        {{-- Every row opens the same tracking page a student
                             gets — including the Official Receipt once the
                             payment has been recorded. --}}
                        <a href="{{ route('student.documents.show', $request) }}"
                           class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition">
                            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-800 truncate">
                                    {{ $request->documentsSummary() ?: 'Document Request' }}
                                </p>
                                <p class="text-xs text-gray-400 truncate">
                                    {{ $request->request_number }}
                                    · Submitted {{ $request->submitted_at?->format('M d, Y') ?? $request->created_at->format('M d, Y') }}
                                    @if ($request->isPaid())
                                        · OR No. {{ $request->or_number ?? '—' }}
                                    @endif
                                </p>
                            </div>

                            <span class="inline-flex items-center gap-1.5 shrink-0
                                         px-3 py-1.5
                                         rounded-full text-xs font-bold
                                         {{ $status['class'] }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                {{ $status['label'] }}
                            </span>

                            <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-400">No document requests yet. Start a new request.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

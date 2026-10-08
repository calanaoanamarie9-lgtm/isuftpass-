<x-app-layout>
    @php
        $user = Auth::user();
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
                    @forelse ($documents ?? [] as $document)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-800">{{ $document['type'] ?? 'Document Request' }}</p>
                                <p class="text-xs text-gray-400">{{ $document['date'] ?? '' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $document['badge']['class'] ?? 'bg-blue-50 text-blue-700' }}">{{ $document['status'] ?? 'Pending' }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-400">No document requests yet. Start a new request.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

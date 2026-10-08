<x-app-layout>
    @php
        $user = Auth::user();
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-gray-900">My Alumni Pass</h1>
                <p class="mt-2 text-sm text-gray-500">Show your QR code at the gate for contactless campus entry.</p>
            </div>

            <div class="bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 rounded-2xl p-8 text-white shadow-lg">
                @if (isset($qrCode))
                    <div class="flex flex-col items-center gap-4">
                        <img src="{{ $qrCode }}" alt="Alumni Pass QR Code" class="w-48 h-48 rounded-xl bg-white p-2">
                        <p class="text-sm text-blue-200">Scan at the gate to verify your alumni ID.</p>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-sm text-blue-200">You do not have an active alumni pass yet.</p>
                        <a href="{{ route('alumni.documents.new') }}"
                           class="mt-6 inline-flex items-center justify-center px-6 py-3 bg-yellow-400 text-blue-950 font-bold rounded-xl hover:bg-yellow-300 transition">
                            Request Alumni Pass
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

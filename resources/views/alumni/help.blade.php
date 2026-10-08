<x-app-layout>
    @php
        $user = Auth::user();
    @endphp

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-gray-900">Help &amp; FAQs</h1>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-100">
                <div class="px-6 py-5">
                    <p class="font-semibold text-gray-900">How do I get my alumni pass?</p>
                    <p class="mt-1 text-sm text-gray-500">Request your pass from the Alumni Office. It will appear on your dashboard once approved.</p>
                </div>
                <div class="px-6 py-5">
                    <p class="font-semibold text-gray-900">How do I book an appointment?</p>
                    <p class="mt-1 text-sm text-gray-500">Open "My Appointments" and choose an office visit slot that suits your schedule.</p>
                </div>
                <div class="px-6 py-5">
                    <p class="font-semibold text-gray-900">Which documents can I request?</p>
                    <p class="mt-1 text-sm text-gray-500">Transcripts, diplomas, certificates of graduation, and other official alumni documents.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

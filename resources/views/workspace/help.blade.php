<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">Help &amp; System Documentation</h1>
                <p class="text-sm text-gray-500 mt-1">Guide on handling appointments and QR check-in for the {{ $office }} office.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="font-bold text-gray-900 mb-3">📷 QR Scanner &amp; Check-in</h2>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Navigate to <strong>QR Scanner &amp; Check-in</strong> from the sidebar.</li>
                        <li>Ask the student to present their QR pass.</li>
                        <li>Scan or enter the token manually.</li>
                        <li>The system will verify the appointment details and mark attendance.</li>
                    </ol>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="font-bold text-gray-900 mb-3">📅 Manage Appointments</h2>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Go to <strong>Manage Appointments</strong> to view all {{ $office }} student visits.</li>
                        <li>Approve pending appointments or reschedule if needed.</li>
                        <li>Cancel appointments that are no longer valid.</li>
                    </ol>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="font-bold text-gray-900 mb-3">🕒 Time Slot Settings</h2>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Go to <strong>Availability</strong> to configure available dates.</li>
                        <li>Set a date to <strong>Open All Day</strong>, <strong>Closed</strong>, or select <strong>Specific Slots</strong>.</li>
                        <li>Click <strong>Save Availability</strong> to apply changes.</li>
                    </ol>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="font-bold text-gray-900 mb-3">📋 Consultation Services</h2>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>View active appointment types configured for {{ $office }}.</li>
                        <li>Contact the administrator to add or modify service types.</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

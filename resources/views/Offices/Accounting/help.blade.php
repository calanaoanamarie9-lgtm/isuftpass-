<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">Help &amp; System Documentation</h1>
                <p class="text-sm text-gray-500 mt-1">Guide on handling appointments and QR check-in for the {{ $office }} office.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="flex items-center gap-2 font-bold text-gray-900 mb-3"><svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/></svg> QR Scanner &amp; Check-in</h2>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Navigate to <strong>QR Scanner &amp; Check-in</strong> from the sidebar.</li>
                        <li>Ask the student to present their QR pass.</li>
                        <li>Scan or enter the token manually.</li>
                        <li>The system will verify the appointment details and mark attendance.</li>
                    </ol>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="flex items-center gap-2 font-bold text-gray-900 mb-3"><svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg> Manage Appointments</h2>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Go to <strong>Manage Appointments</strong> to view all {{ $office }} student visits.</li>
                        <li>Approve pending appointments or reschedule if needed.</li>
                        <li>Cancel appointments that are no longer valid.</li>
                    </ol>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="flex items-center gap-2 font-bold text-gray-900 mb-3"><svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg> Availability</h2>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Go to <strong>Availability</strong> to configure available dates.</li>
                        <li>Set a date to <strong>Open All Day</strong> or <strong>Closed</strong>.</li>
                        <li>Click <strong>Save Availability</strong> to apply changes.</li>
                    </ol>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="flex items-center gap-2 font-bold text-gray-900 mb-3"><svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Zm0 0v.75m9-9v.75m-9 3h.008M9.75 9h.008M9.75 12h.008M9.75 15h.008M14.25 9h.008M14.25 12h.008M14.25 15h.008"/></svg> Consultation Services</h2>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>View active appointment types configured for {{ $office }}.</li>
                        <li>Contact the administrator to add or modify service types.</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
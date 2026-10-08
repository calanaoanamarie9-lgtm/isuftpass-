<x-app-layout>
    @php
        $user = Auth::user();
    @endphp

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-gray-900">My Profile</h1>
                <p class="mt-2 text-sm text-gray-500">Your alumni information &amp; credentials.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Account Details</h2>
                </div>
                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-semibold text-gray-900">{{ $user->name }}</dd>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-semibold text-gray-900">{{ $user->email }}</dd>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-between">
                        <dt class="text-gray-500">Registration Type</dt>
                        <dd class="font-semibold text-gray-900">Alumni</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>

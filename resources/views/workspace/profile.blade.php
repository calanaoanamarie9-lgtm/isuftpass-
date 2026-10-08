<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">{{ $office }} Profile Settings</h1>
                <p class="text-sm text-gray-500 mt-1">Update credentials and office information for the {{ $office }} account.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Account Information</h2>
                </div>
                <div class="p-6 text-sm space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Name</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Email</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Office</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $office }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Role</p>
                        <p class="mt-1 font-semibold text-gray-900 capitalize">{{ $user->role }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="inline-block px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
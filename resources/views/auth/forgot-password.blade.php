<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Password reset by email is turned off. Please contact the system administrator to have your password reset.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <x-input-error :messages="$errors->all()" class="mt-2" />

    <div class="flex items-center justify-end mt-4">
        <a
            href="{{ route('login') }}"
            class="text-sm font-semibold text-blue-600 hover:text-blue-800"
        >
            {{ __('Back to login') }}
        </a>
    </div>
</x-guest-layout>

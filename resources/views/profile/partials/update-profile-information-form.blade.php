@php
    // Parents and guests signed up without a name, so these fields are offered
    // but not demanded from them - a required field they have nothing to put
    // in would block every save on this card.
    $requiresName = auth()->user()->hasStructuredName();
@endphp

<section>
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label :value="__('Full Name')" />

            <div class="mt-1 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="last_name" :value="__('Last Name / Family Name')" />
                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', \Illuminate\Support\Facades\Auth::user()->last_name)" {{ $requiresName ? 'required' : '' }} autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>

                <div>
                    <x-input-label for="first_name" :value="__('First Name')" />
                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', \Illuminate\Support\Facades\Auth::user()->first_name)" {{ $requiresName ? 'required' : '' }} />
                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                </div>

                <div>
                    <x-input-label for="middle_name" :value="__('Middle Name')" />
                    <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" :value="old('middle_name', \Illuminate\Support\Facades\Auth::user()->middle_name)" {{ $requiresName ? 'required' : '' }} />
                    <x-input-error class="mt-2" :messages="$errors->get('middle_name')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', \Illuminate\Support\Facades\Auth::user()->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

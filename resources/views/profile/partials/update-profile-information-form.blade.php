@php
    // Parents and guests gave one Full Name at sign-up and nothing is ever
    // issued in their name, so the three-part split is offered to them rather
    // than demanded - a required field they have nothing to put in would
    // block every save on this card, and their display name is already set.
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
                    {{-- No {{ }} echo inside these tags: Blade cannot parse a
                         component tag that carries one, so it was emitted as
                         literal <x-text-input> markup and the browser produced
                         no input at all - the name fields simply did not
                         exist. Bound attributes only. --}}
                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', auth()->user()->last_name)" :required="$requiresName" autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>

                <div>
                    <x-input-label for="first_name" :value="__('First Name')" />
                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', auth()->user()->first_name)" :required="$requiresName" />
                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                </div>

                <div>
                    <x-input-label for="middle_name" :value="__('Middle Name')" />
                    <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" :value="old('middle_name', auth()->user()->middle_name)" :required="$requiresName" />
                    <x-input-error class="mt-2" :messages="$errors->get('middle_name')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', auth()->user()->email)" required autocomplete="username" />
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

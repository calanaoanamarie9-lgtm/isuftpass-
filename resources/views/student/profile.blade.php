<x-app-layout>

    <div class="min-h-screen bg-slate-50 py-8 sm:py-10">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                 PAGE HEADER
            ========================================================== --}}
            <div class="mb-8">

                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold
                          text-blue-700 hover:text-blue-900 transition mb-5">

                    <svg class="w-5 h-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 19l-7-7 7-7"/>
                    </svg>

                    Back
                </a>


                <div class="flex flex-col sm:flex-row sm:items-center
                            justify-between gap-5">

                    <div class="flex items-center gap-4">

                        {{-- ISUFSTPASS LOGO --}}
                        <div class="w-16 h-16 rounded-2xl bg-white
                                    border border-slate-200 shadow-sm
                                    flex items-center justify-center
                                    overflow-hidden">

                            <img
                                src="{{ asset('img/isufstpass-logo.png') }}"
                                alt="ISUFSTPASS Logo"
                                class="w-14 h-14 object-contain"
                            >

                        </div>


                        <div>

                            <p class="text-sm font-extrabold tracking-wide
                                      text-blue-700 uppercase">
                                ISUFSTPASS
                            </p>

                            <h1 class="mt-1 text-2xl sm:text-3xl
                                       font-extrabold text-slate-900">
                                Account & Password Settings
                            </h1>

                            <p class="mt-1 text-sm text-slate-500">
                                Manage your profile, account information,
                                and security.
                            </p>

                        </div>

                    </div>


                    {{-- VERIFIED BADGE --}}
                    <div class="inline-flex items-center gap-2 self-start
                                sm:self-center rounded-full bg-blue-50
                                border border-blue-100 px-4 py-2">

                        <span class="flex items-center justify-center
                                     w-6 h-6 rounded-full bg-blue-700">

                            <svg class="w-3.5 h-3.5 text-white"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="3"
                                      d="M5 13l4 4L19 7"/>
                            </svg>

                        </span>

                        <span class="text-xs font-bold text-blue-800">
                            ISUFSTPASS ACCOUNT
                        </span>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                 SUCCESS MESSAGE
            ========================================================== --}}
            @if (session('status'))

                <div class="mb-6 overflow-hidden rounded-2xl
                            border border-green-200 bg-white shadow-sm">

                    <div class="flex items-center gap-4 px-5 py-4">

                        <div class="w-10 h-10 rounded-xl bg-green-100
                                    flex items-center justify-center shrink-0">

                            <svg class="w-5 h-5 text-green-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5 13l4 4L19 7"/>

                            </svg>

                        </div>

                        <div>

                            <p class="text-sm font-bold text-green-800">
                                Successfully updated
                            </p>

                            <p class="text-xs text-green-700 mt-0.5">
                                {{ session('status') }}
                            </p>

                        </div>

                    </div>

                </div>

            @endif



            {{-- =========================================================
                 PROFILE PICTURE CARD
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-slate-200
                        shadow-sm overflow-hidden mb-6">

                {{-- Card Header --}}
                <div class="px-6 sm:px-8 py-6
                            bg-gradient-to-r from-blue-50 via-white to-white
                            border-b border-blue-100">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-blue-800
                                    flex items-center justify-center
                                    shadow-sm">

                            <svg class="w-6 h-6 text-white"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0z
                                         M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>

                            </svg>

                        </div>

                        <div>

                            <h2 class="text-lg font-bold text-slate-900">
                                Profile Picture
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Add a photo so your identity can be recognized
                                throughout ISUFSTPASS.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Profile Content --}}
                <form method="POST"
                      action="{{ route('student.profile.update') }}"
                      enctype="multipart/form-data"
                      class="p-6 sm:p-8">

                    @csrf
                    @method('PUT')


                    {{-- The photo can live on the student profile (uploaded on
                         this very card) or on the user record (uploaded during
                         registration) — render whichever exists so the card
                         never falls back to the initial while a photo is set. --}}
                    @php
                        $avatarUrl = $profile->avatar_url ?? Auth::user()->avatar_url;
                    @endphp


                    <div class="flex flex-col sm:flex-row
                                items-start sm:items-center gap-7">


                        {{-- Avatar --}}
                        <div class="shrink-0">

                            <div id="avatar-preview"
                                 class="w-28 h-28 rounded-2xl
                                        bg-blue-50
                                        flex items-center justify-center
                                        text-blue-700
                                        font-extrabold text-4xl
                                        uppercase overflow-hidden
                                        border-4 border-white
                                        ring-1 ring-blue-100
                                        shadow-md">

                                @if ($avatarUrl)

                                    <img
                                        src="{{ $avatarUrl }}"
                                        alt="Profile Picture"
                                        class="w-full h-full object-cover"
                                    >

                                @else

                                    {{ substr(Auth::user()->name, 0, 1) }}

                                @endif

                            </div>

                        </div>


                        {{-- Upload Controls --}}
                        <div class="flex-1 w-full">

                            <div class="mb-4">

                                <p class="text-base font-bold text-slate-900">
                                    Your profile photo
                                </p>

                                <p class="text-sm text-slate-500 mt-1">
                                    Use a clear and appropriate photo for your
                                    ISUFSTPASS profile.
                                </p>

                            </div>


                            <div class="flex flex-wrap items-center gap-3">

                                <label for="avatar"
                                       class="inline-flex items-center gap-2
                                              px-4 py-2.5 rounded-xl
                                              bg-blue-800 text-white
                                              text-sm font-bold
                                              hover:bg-blue-900
                                              cursor-pointer
                                              transition shadow-sm">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0
                                                 012.828 0L16 16m-2-2l1.586-1.586a2
                                                 2 0 012.828 0L20 14m-6-6h.01
                                                 M6 20h12a2 2 0 002-2V6a2
                                                 2 0 00-2-2H6a2 2 0
                                                 00-2 2v12a2 2 0 002 2z"/>

                                    </svg>

                                    Choose Photo

                                    <input
                                        id="avatar"
                                        name="avatar"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="hidden"
                                    >

                                </label>


                                @if ($avatarUrl)

                                    <button
                                        type="button"
                                        x-data
                                        x-on:click.prevent="
                                            document.getElementById('remove-avatar').submit()
                                        "
                                        class="inline-flex items-center gap-2
                                               px-4 py-2.5 rounded-xl
                                               bg-red-50 text-red-700
                                               border border-red-100
                                               text-sm font-bold
                                               hover:bg-red-100
                                               transition">

                                        <svg class="w-4 h-4"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2
                                                     0 0116.138 21H7.862a2
                                                     2 0 01-1.995-1.858L5
                                                     7m5 4v6m4-6v6M9 7V4a1
                                                     1 0 011-1h4a1 1 0 011
                                                     1v3m-7 0h8"/>

                                        </svg>

                                        Remove
                                    </button>

                                @endif

                            </div>


                            <p class="mt-3 text-xs text-slate-400">
                                JPG, PNG or WEBP • Maximum file size: 2 MB
                            </p>


                            <div class="mt-5">

                                <x-primary-button>
                                    Upload Photo
                                </x-primary-button>

                            </div>


                            @error('avatar')

                                <p class="mt-3 text-sm text-red-600 font-medium">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </form>


                {{-- Remove Avatar Form --}}
                @if ($avatarUrl)

                    <form id="remove-avatar"
                          method="POST"
                          action="{{ route('student.profile.update') }}"
                          class="hidden">

                        @csrf
                        @method('PUT')

                        <input type="hidden"
                               name="remove_avatar"
                               value="1">

                    </form>

                @endif

            </div>



            {{-- =========================================================
                 ACCOUNT INFORMATION CARD
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-slate-200
                        shadow-sm overflow-hidden mb-6">

                <div class="px-6 sm:px-8 py-6
                            bg-gradient-to-r from-blue-50 via-white to-white
                            border-b border-blue-100">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-blue-800
                                    flex items-center justify-center
                                    shadow-sm">

                            <svg class="w-6 h-6 text-white"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0
                                         018 0z
                                         M12 14a7 7 0 00-7 7h14a7
                                         7 0 00-7-7z"/>

                            </svg>

                        </div>


                        <div>

                            <h2 class="text-lg font-bold text-slate-900">
                                Account Information
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Update your name and email address.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-6 sm:p-8">

                    @include('profile.partials.update-profile-information-form')

                </div>

            </div>



            {{-- =========================================================
                 SECURITY / PASSWORD CARD
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-slate-200
                        shadow-sm overflow-hidden mb-6">

                <div class="px-6 sm:px-8 py-6
                            bg-gradient-to-r from-blue-50 via-white to-white
                            border-b border-blue-100">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-blue-800
                                    flex items-center justify-center
                                    shadow-sm">

                            <svg class="w-6 h-6 text-white"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0
                                         002-2v-6a2 2 0 00-2-2H6a2
                                         2 0 00-2 2v6a2 2 0
                                         002 2zm10-10V7a4 4 0
                                         00-8 0v4h8z"/>

                            </svg>

                        </div>


                        <div>

                            <h2 class="text-lg font-bold text-slate-900">
                                Security
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Keep your ISUFSTPASS account secure with
                                a strong password.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-6 sm:p-8">

                    @include('profile.partials.update-password-form')

                </div>

            </div>



            {{-- =========================================================
                 SECURITY NOTICE
            ========================================================== --}}
            <div class="mb-6 rounded-2xl border border-yellow-200
                        bg-gradient-to-r from-yellow-50 to-white
                        overflow-hidden">

                <div class="p-5 sm:p-6 flex items-start gap-4">

                    <div class="w-11 h-11 rounded-xl bg-yellow-400
                                flex items-center justify-center
                                shrink-0 shadow-sm">

                        <svg class="w-5 h-5 text-blue-900"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 9v4m0 4h.01
                                     M10.29 3.86l-8.82 15a2
                                     2 0 001.71 3h17.64a2
                                     2 0 001.71-3l-8.82-15a2
                                     2 0 00-3.42 0z"/>

                        </svg>

                    </div>


                    <div>

                        <h3 class="text-sm font-bold text-yellow-900">
                            Keep your account secure
                        </h3>

                        <p class="text-sm text-yellow-800 mt-1 leading-6">
                            Never share your ISUFSTPASS password or QR Pass
                            with other people. Your account contains
                            important student and transaction information.
                        </p>

                    </div>

                </div>

            </div>



            {{-- =========================================================
                 DANGER ZONE
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-red-200
                        shadow-sm overflow-hidden">

                <div class="px-6 sm:px-8 py-6
                            bg-red-50 border-b border-red-100">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-red-600
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-white"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 9v4m0 4h.01
                                         M10.29 3.86l-8.82 15a2
                                         2 0 001.71 3h17.64a2
                                         2 0 001.71-3l-8.82-15a2
                                         2 0 00-3.42 0z"/>

                            </svg>

                        </div>


                        <div>

                            <h2 class="text-lg font-bold text-slate-900">
                                Danger Zone
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Permanently delete your ISUFSTPASS account.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-6 sm:p-8">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center sm:justify-between gap-5">

                        <div>

                            <p class="font-bold text-slate-900">
                                Delete Account
                            </p>

                            <p class="text-sm text-slate-500 mt-1 max-w-xl">
                                Once your account is deleted, all of its
                                resources and associated data may be
                                permanently removed.
                            </p>

                        </div>


                        <div class="shrink-0">

                            @include('profile.partials.delete-user-form')

                        </div>

                    </div>

                </div>

            </div>



            {{-- =========================================================
                 FOOTER
            ========================================================== --}}
            <div class="mt-8">

                <div class="rounded-2xl bg-gradient-to-r
                            from-blue-900 to-blue-800
                            px-6 py-5 shadow-sm">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center sm:justify-between gap-4">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10 rounded-xl bg-yellow-400
                                        flex items-center justify-center">

                                <svg class="w-5 h-5 text-blue-900"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12l2 2 4-4
                                             m5.618-4.016A11.955
                                             11.955 0 0112 2.944a11.955
                                             11.955 0 01-8.618 3.04A12.02
                                             12.02 0 003 9c0 5.591
                                             3.824 10.29 9 11.622
                                             5.176-1.332 9-6.03
                                             9-11.622 0-1.042-.133-2.052-.382
                                             -3.016z"/>

                                </svg>

                            </div>


                            <div>

                                <p class="text-sm font-bold text-white">
                                    ISUFSTPASS
                                </p>

                                <p class="text-xs text-blue-200">
                                    Secure • Reliable • Official
                                </p>

                            </div>

                        </div>


                        <p class="text-xs text-blue-200">
                            Iloilo State University of Fisheries Science
                            and Technology
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- ================================================================
         AVATAR LIVE PREVIEW
    ================================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const input = document.getElementById('avatar');
            const preview = document.getElementById('avatar-preview');

            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', function () {

                const file = this.files && this.files[0];

                if (!file) {
                    return;
                }

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a JPG, PNG, or WEBP image.');
                    this.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('The image must not exceed 2 MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (event) {

                    preview.innerHTML = `
                        <img
                            src="${event.target.result}"
                            alt="Profile Picture"
                            class="w-full h-full object-cover"
                        >
                    `;

                };

                reader.readAsDataURL(file);

            });

        });
    </script>

</x-app-layout>
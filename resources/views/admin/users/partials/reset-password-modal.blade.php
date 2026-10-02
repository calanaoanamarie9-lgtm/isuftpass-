{{-- =========================================================
    ISUFSTPASS - RESET PASSWORD MODAL

    Open from anywhere on the users page with:

        $dispatch('open-reset-password', {
            action: {{ route('admin.users.password', $user) }},
            email:  {{ $user->email }}
        })

    The admin sets the value directly instead of mailing a reset
    link, so this has to work with the mailer down - which is the
    normal case for a student standing at the counter.

    Submitting goes through the shared data-confirm handler in
    resources/js/app.js (SweetAlert2), which reads the bound
    data-confirm-* attributes at click time.
========================================================= --}}

<style>
    [x-cloak] {
        display: none !important;
    }
</style>


<div
    x-data="resetPasswordModal()"
    @open-reset-password.window="start($event.detail)"
    x-cloak
    x-show="open"
    class="fixed inset-0 z-50"
    style="display: none;"
>

    {{-- =====================================================
        BACKDROP
    ====================================================== --}}
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        @click="close()"
        class="absolute inset-0 bg-[#071a38]/70 backdrop-blur-sm"
    ></div>


    {{-- =====================================================
        MODAL CONTAINER
    ====================================================== --}}
    <div class="absolute inset-0 overflow-y-auto">

        <div class="min-h-full flex items-center justify-center p-4 sm:p-6">

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-5 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-5 scale-95"
                class="relative w-full max-w-md
                       bg-white
                       rounded-3xl
                       shadow-2xl
                       overflow-hidden"
            >

                {{-- =================================================
                    HEADER
                ================================================== --}}
                <div class="relative
                            bg-gradient-to-r
                            from-[#102d5b]
                            via-blue-900
                            to-[#0b4ea2]
                            px-6 sm:px-7
                            py-6">

                    {{-- Decorative accent --}}
                    <div class="absolute -right-14 -top-16
                                w-44 h-44
                                rounded-full
                                bg-white/5">
                    </div>


                    <div class="relative flex items-start justify-between gap-4">

                        <div class="flex items-center gap-4">

                            {{-- ICON --}}
                            <div class="w-12 h-12
                                        rounded-2xl
                                        bg-white/10
                                        border border-white/10
                                        flex items-center justify-center
                                        shrink-0">

                                <svg class="w-6 h-6 text-yellow-300"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M17 16l4 4m0 0l-4 4m4-4H7m6 4v-3a2 2 0 00-2-2H7a2 2 0 00-2 2v3m10-9V7a2 2 0 00-2-2H9a2 2 0 00-2 2v4"/>

                                </svg>

                            </div>


                            <div>

                                <p class="text-[11px]
                                          font-bold
                                          uppercase
                                          tracking-wider
                                          text-blue-200">

                                    Admin Action

                                </p>

                                <h3 class="text-lg
                                           font-extrabold
                                           text-white">

                                    Reset Password

                                </h3>

                            </div>

                        </div>


                        {{-- CLOSE (outside the form, so the global confirm
                             handler in app.js skips it) --}}
                        <button
                            type="button"
                            @click="close()"
                            aria-label="Close"
                            class="w-9 h-9
                                   rounded-xl
                                   bg-white/10
                                   hover:bg-white/20
                                   text-white
                                   flex items-center justify-center
                                   transition
                                   shrink-0"
                        >

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>

                            </svg>

                        </button>

                    </div>

                </div>


                {{-- =================================================
                    FORM
                ================================================== --}}
                <form method="POST"
                      :action="action"
                      :data-confirm="'This replaces the password for ' + email + '. Whoever holds the new value can sign in until they change it.'"
                      :data-confirm-title="'Reset password for ' + email + '?'"
                      data-confirm-ok="Yes, reset it"
                      data-confirm-icon="warning">

                    @csrf
                    @method('PUT')


                    <div class="px-6 sm:px-7
                                py-6
                                space-y-5">

                        {{-- =================================================
                            TARGET ACCOUNT
                        ================================================== --}}
                        <div class="rounded-2xl
                                    bg-gray-50
                                    border border-gray-100
                                    px-4 py-3">

                            <p class="text-[10px]
                                      font-bold
                                      uppercase
                                      tracking-wider
                                      text-gray-400">

                                Account

                            </p>

                            <p class="mt-0.5
                                      text-sm
                                      font-bold
                                      text-gray-800
                                      break-all"
                               x-text="email || '—'">
                            </p>

                        </div>


                        {{-- =================================================
                            NEW PASSWORD
                        ================================================== --}}
                        <div>

                            <label for="reset-password-input"
                                   class="block text-xs
                                          font-bold
                                          uppercase
                                          tracking-wider
                                          text-gray-500">

                                New Password

                            </label>

                            <div class="mt-1.5 relative">

                                <input id="reset-password-input"
                                       name="password"
                                       :type="show ? 'text' : 'password'"
                                       x-model="password"
                                       autocomplete="new-password"
                                       placeholder="At least 8 characters"
                                       class="w-full
                                              rounded-xl
                                              border border-gray-200
                                              bg-white
                                              px-4 py-3 pr-16
                                              text-sm text-gray-800
                                              placeholder-gray-400
                                              focus:border-blue-600
                                              focus:ring-2 focus:ring-blue-600/20
                                              outline-none
                                              transition">

                                {{-- Reveal toggle: the admin has to be able to
                                     read the value out to the person. --}}
                                <button
                                    type="button"
                                    @click="show = !show"
                                    x-text="show ? 'Hide' : 'Show'"
                                    class="absolute inset-y-0 right-0
                                           px-4
                                           text-[11px]
                                           font-bold
                                           uppercase
                                           tracking-wider
                                           text-gray-400
                                           hover:text-blue-700
                                           transition"
                                >
                                </button>

                            </div>

                        </div>


                        {{-- =================================================
                            CONFIRM PASSWORD
                        ================================================== --}}
                        <div>

                            <label for="reset-password-confirm"
                                   class="block text-xs
                                          font-bold
                                          uppercase
                                          tracking-wider
                                          text-gray-500">

                                Confirm Password

                            </label>

                            <input id="reset-password-confirm"
                                   name="password_confirmation"
                                   :type="show ? 'text' : 'password'"
                                   x-model="confirmation"
                                   autocomplete="new-password"
                                   placeholder="Re-enter the password"
                                   class="mt-1.5
                                          w-full
                                          rounded-xl
                                          border border-gray-200
                                          bg-white
                                          px-4 py-3
                                          text-sm text-gray-800
                                          placeholder-gray-400
                                          focus:border-blue-600
                                          focus:ring-2 focus:ring-blue-600/20
                                          outline-none
                                          transition">

                        </div>


                        {{-- =================================================
                            GENERATE
                        ================================================== --}}
                        <button
                            type="button"
                            @click="generate()"
                            class="inline-flex items-center gap-2
                                   rounded-lg
                                   border border-dashed border-gray-300
                                   bg-gray-50
                                   px-3 py-2
                                   text-xs font-bold
                                   text-gray-600
                                   hover:border-blue-400
                                   hover:bg-blue-50
                                   hover:text-blue-700
                                   transition"
                        >

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>

                            </svg>

                            Generate a secure password

                        </button>


                        <p class="text-xs
                                  leading-5
                                  text-gray-400">

                            Replaces the current password immediately. Hand the
                            value over privately and have them change it after
                            they sign in.

                        </p>

                    </div>


                    {{-- =================================================
                        FOOTER
                    ================================================== --}}
                    <div class="px-6 sm:px-7
                                pb-6
                                pt-2
                                flex flex-col-reverse
                                sm:flex-row
                                sm:items-center
                                sm:justify-end
                                gap-3">

                        {{-- CANCEL --}}
                        <button
                            type="button"
                            @click="close()"
                            class="w-full sm:w-auto
                                   px-6 py-3
                                   rounded-xl
                                   bg-white
                                   border border-gray-200
                                   text-sm
                                   font-bold
                                   text-gray-600
                                   hover:bg-gray-50
                                   hover:text-gray-800
                                   transition"
                        >

                            Cancel

                        </button>


                        {{-- SUBMIT --}}
                        <button
                            type="submit"
                            :disabled="!action || !password"
                            class="w-full sm:w-auto
                                   inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   px-6 py-3
                                   rounded-xl
                                   bg-blue-700
                                   text-white
                                   text-sm
                                   font-bold
                                   hover:bg-blue-800
                                   shadow-sm
                                   transition
                                   disabled:opacity-50
                                   disabled:cursor-not-allowed"
                        >

                            Reset Password

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>



{{-- =========================================================
    ALPINE JS
========================================================= --}}
<script>

function resetPasswordModal() {

    return {

        open: false,

        action: '',
        email: '',

        password: '',
        confirmation: '',

        show: false,


        start(detail) {

            this.action = detail.action || '';
            this.email = detail.email || '';

            this.password = '';
            this.confirmation = '';

            this.show = false;

            this.open = true;

        },


        close() {
            this.open = false;
        },


        /**
         * 16 characters, rejection-sampled from a 36-symbol alphabet that
         * drops lookalikes (0/O, 1/l/I). Rejecting bytes >= floor(256/36)*36
         * keeps modulo bias out of it, and the result is short enough to read
         * aloud across a counter.
         */
        generate() {

            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            const limit = Math.floor(256 / alphabet.length) * alphabet.length;

            let value = '';

            while (value.length < 16) {

                const bytes = new Uint8Array(64);
                crypto.getRandomValues(bytes);

                for (const byte of bytes) {

                    if (byte >= limit) {
                        continue;
                    }

                    value += alphabet[byte % alphabet.length];

                    if (value.length === 16) {
                        break;
                    }

                }

            }

            this.password = value;
            this.confirmation = value;
            this.show = true;

        },

    };

}

</script>

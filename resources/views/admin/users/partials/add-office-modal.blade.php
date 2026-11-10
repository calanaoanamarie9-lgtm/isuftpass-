{{-- =========================================================
    ISUFSTPASS - ADD OFFICE ACCOUNT MODAL

    Allows the System Administrator to create accounts directly
    for university offices and departments.
========================================================= --}}

<div
    x-data="addOfficeModal()"
    @open-add-office.window="openModal()"
    x-cloak
    x-show="open"
    class="fixed inset-0 z-50"
    style="display: none;"
>

    {{-- BACKDROP --}}
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        @click="close()"
        class="absolute inset-0 bg-[#071a38]/70 backdrop-blur-sm"
    ></div>

    {{-- MODAL CONTAINER --}}
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
                class="relative w-full max-w-xl
                       bg-white
                       rounded-3xl
                       shadow-2xl
                       overflow-hidden"
            >

                {{-- HEADER --}}
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
                                          stroke-width="2"
                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>

                                </svg>

                            </div>

                            <div>

                                <p class="text-xs
                                          font-bold
                                          uppercase
                                          tracking-widest
                                          text-yellow-300">
                                    Administrator Action
                                </p>

                                <h3 class="text-xl
                                           font-black
                                           text-white
                                           mt-0.5">
                                    Create Office Account
                                </h3>

                                <p class="text-xs
                                          text-blue-100
                                          mt-1">
                                    Provision an official account for university personnel &amp; offices.
                                </p>

                            </div>

                        </div>

                        {{-- CLOSE BUTTON --}}
                        <button
                            type="button"
                            @click="close()"
                            class="w-9 h-9
                                   rounded-xl
                                   bg-white/10
                                   hover:bg-white/20
                                   text-white
                                   flex items-center justify-center
                                   transition"
                            aria-label="Close"
                        >
                            ✕
                        </button>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-yellow-400"></div>

                </div>

                {{-- FORM BODY --}}
                <form
                    method="POST"
                    action="{{ route('admin.users.store') }}"
                    class="p-6 sm:p-7 space-y-5"
                >
                    @csrf
                    <input type="hidden" name="_is_add_office" value="1">

                    {{-- NOTICE --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">
                            i
                        </div>
                        <p class="text-xs text-blue-900 leading-relaxed">
                            Accounts created here are <span class="font-bold text-blue-950">immediately activated and approved</span>. The designated office staff can log in using the email and password you set below.
                        </p>
                    </div>

                    {{-- 1. OFFICE SELECTION --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                            Office / Department <span class="text-red-500">*</span>
                        </label>

                        <div class="space-y-2">
                            <select
                                x-model="selectedOffice"
                                @change="onOfficeChange()"
                                class="w-full rounded-xl border-gray-300 bg-gray-50 focus:bg-white text-sm focus:border-blue-600 focus:ring-blue-600 font-medium"
                            >
                                <option value="" disabled selected>— Select an Office / Department —</option>
                                @foreach ($availableOffices ?? [] as $key => $label)
                                    <option value="{{ is_numeric($key) ? $label : $key }}">{{ $label }}</option>
                                @endforeach
                                <option value="__CUSTOM__">➕ Other / Custom Office (Type Name)</option>
                            </select>

                            {{-- Custom office input if "Other" is picked --}}
                            <div x-show="isCustomOffice" x-cloak class="pt-1">
                                <input
                                    type="text"
                                    x-model="customOfficeName"
                                    x-ref="customOfficeInput"
                                    :required="isCustomOffice"
                                    placeholder="Type the full name of the new office"
                                    class="w-full rounded-xl border-amber-300 bg-amber-50/40 text-sm focus:border-amber-500 focus:ring-amber-500"
                                >
                                <p class="text-[11px] text-amber-700 mt-1">
                                    This office will be automatically added to the university directory.
                                </p>
                            </div>

                            <input type="hidden" name="office" :value="finalOfficeName">
                        </div>
                    </div>

                    {{-- 2. ROLE SELECTION --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                            Account Role <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:border-blue-500 transition"
                                   :class="role === 'office' ? 'border-blue-600 bg-blue-50/50' : 'bg-white'">
                                <input type="radio" name="role" value="office" x-model="role" class="text-blue-600 focus:ring-blue-500">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Office Staff</p>
                                    <p class="text-[10px] text-gray-500">General office workspace</p>
                                </div>
                            </label>

                            <label class="relative flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:border-blue-500 transition"
                                   :class="role === 'department' ? 'border-blue-600 bg-blue-50/50' : 'bg-white'">
                                <input type="radio" name="role" value="department" x-model="role" class="text-blue-600 focus:ring-blue-500">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Department</p>
                                    <p class="text-[10px] text-gray-500">College / academic dept.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 3. NAME & EMAIL --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                Personnel / In-Charge <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                required
                                value="{{ old('name') }}"
                                placeholder="e.g. Maria Santos / Office In-Charge"
                                class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                Login Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                required
                                value="{{ old('email') }}"
                                placeholder="e.g. office@isufst.edu.ph"
                                class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                            >
                        </div>
                    </div>

                    {{-- 4. POSITION & CONTACT --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                Position / Title
                            </label>
                            <input
                                type="text"
                                name="position"
                                value="{{ old('position') }}"
                                placeholder="e.g. Office Head / Coordinator"
                                class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                Contact Number
                            </label>
                            <input
                                type="text"
                                name="contact_number"
                                value="{{ old('contact_number') }}"
                                placeholder="e.g. 09123456789"
                                class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                            >
                        </div>
                    </div>

                    {{-- 5. PASSWORD & CONFIRMATION --}}
                    <div class="pt-2 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-600">
                                Password <span class="text-red-500">*</span>
                            </label>

                            <button
                                type="button"
                                @click="generatePassword()"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 hover:text-blue-900 transition"
                            >
                                <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                                <span>Generate Password</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    x-model="password"
                                    required
                                    minlength="8"
                                    placeholder="Minimum 8 characters"
                                    class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                                >
                            </div>

                            <div>
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password_confirmation"
                                    x-model="confirmation"
                                    required
                                    minlength="8"
                                    placeholder="Confirm password"
                                    class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                                >
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-gray-500 hover:text-gray-700">
                                <input type="checkbox" x-model="showPassword" class="rounded text-blue-600 focus:ring-blue-500">
                                <span>Show password</span>
                            </label>

                            <span x-show="generatedNotice" x-cloak class="text-xs font-semibold text-emerald-600">
                                ✓ Password generated
                            </span>
                        </div>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="close()"
                            class="px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#063b91] hover:bg-[#052f75] text-white text-sm font-bold shadow-sm transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Create Office Account</span>
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

</div>

<script>
function addOfficeModal() {
    return {
        open: {{ ($errors->any() && old('_is_add_office')) ? 'true' : 'false' }},
        selectedOffice: '{{ old('office', '') }}',
        customOfficeName: '',
        isCustomOffice: false,
        role: '{{ old('role', 'office') }}',
        password: '',
        confirmation: '',
        showPassword: false,
        generatedNotice: false,

        openModal() {
            this.open = true;
            this.generatedNotice = false;
        },

        close() {
            this.open = false;
        },

        onOfficeChange() {
            if (this.selectedOffice === '__CUSTOM__') {
                this.isCustomOffice = true;
                this.$nextTick(() => {
                    this.$refs.customOfficeInput?.focus();
                });
            } else {
                this.isCustomOffice = false;
                this.customOfficeName = '';
            }
        },

        get finalOfficeName() {
            return this.isCustomOffice ? this.customOfficeName : this.selectedOffice;
        },

        generatePassword() {
            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            const limit = Math.floor(256 / alphabet.length) * alphabet.length;
            let value = '';

            while (value.length < 12) {
                const bytes = new Uint8Array(64);
                crypto.getRandomValues(bytes);
                for (const byte of bytes) {
                    if (byte >= limit) continue;
                    value += alphabet[byte % alphabet.length];
                    if (value.length === 12) break;
                }
            }

            this.password = value;
            this.confirmation = value;
            this.showPassword = true;
            this.generatedNotice = true;
        }
    };
}
</script>

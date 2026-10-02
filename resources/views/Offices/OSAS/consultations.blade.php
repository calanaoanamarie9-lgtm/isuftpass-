<x-app-layout>

    <div class="min-h-screen bg-[#f4f7fb] py-10">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =====================================================
                HEADER
            ====================================================== --}}
            <div class="mb-8">

                <div class="flex flex-col sm:flex-row
                            sm:items-center
                            sm:justify-between
                            gap-5">

                    <div>

                        <div class="flex items-center gap-3 mb-3">

                            <div class="w-11 h-11
                                        rounded-2xl
                                        bg-blue-950
                                        flex items-center
                                        justify-center
                                        shadow-sm">

                                <svg class="w-5 h-5 text-yellow-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 10h8M8 14h5m7-2a8 8 0 11-16 0c0 1.54.435 2.98 1.19 4.2L4 20l3.8-1.19A8 8 0 0020 12z"/>

                                </svg>

                            </div>

                            <div>

                                <p class="text-[10px]
                                          font-extrabold
                                          uppercase
                                          tracking-[0.18em]
                                          text-blue-600">

                                    ISUFSTPASS

                                </p>

                                <h1 class="text-2xl
                                           sm:text-3xl
                                           font-extrabold
                                           text-blue-950">

                                    {{ $office }} Consultation Services

                                </h1>

                            </div>

                        </div>

                        <p class="text-sm
                                  text-gray-500
                                  max-w-2xl">

                            Appointment types and consultation services
                            available through the {{ $office }} Office.

                        </p>

                    </div>


                    {{-- OFFICE BADGE --}}
                    <div class="inline-flex
                                items-center
                                gap-2
                                self-start
                                px-4 py-2.5
                                rounded-xl
                                bg-white
                                border border-gray-100
                                shadow-sm">

                        <span class="w-2.5 h-2.5
                                     rounded-full
                                     bg-green-500">
                        </span>

                        <span class="text-xs
                                     font-bold
                                     text-gray-600">

                            {{ $office }} Office

                        </span>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                CONSULTATION SERVICES
            ====================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden">


                {{-- CARD HEADER --}}
                <div class="px-6 sm:px-8 py-6
                            border-b border-gray-100
                            bg-gradient-to-r
                            from-blue-950
                            to-blue-900">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12
                                    rounded-2xl
                                    bg-white/10
                                    border border-white/10
                                    flex items-center
                                    justify-center">

                            <svg class="w-6 h-6 text-yellow-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M8 10h8M8 14h5m7-2a8 8 0 11-16 0c0 1.54.435 2.98 1.19 4.2L4 20l3.8-1.19A8 8 0 0020 12z"/>

                            </svg>

                        </div>

                        <div>

                            <p class="text-[10px]
                                      uppercase
                                      tracking-widest
                                      font-bold
                                      text-blue-300">

                                Student Services

                            </p>

                            <h2 class="text-xl
                                       font-extrabold
                                       text-white">

                                Consultation Services

                            </h2>

                            <p class="mt-1
                                      text-xs
                                      text-blue-200">

                                Select the type of consultation you need.

                            </p>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                    SERVICE CARDS
                ================================================== --}}
                <div class="p-6 sm:p-8">

                    <div class="grid grid-cols-1
                                md:grid-cols-2
                                lg:grid-cols-3
                                gap-5">


                        {{-- =================================================
                            STUDENT AFFAIRS
                        ================================================== --}}
                        <div class="col-span-full flex flex-col sm:flex-row
                                    sm:items-center sm:justify-between
                                    gap-3
                                    mb-1">

                            <p class="text-sm text-gray-500">
                                {{ $services->count() }} {{ Str::plural('service', $services->count()) }} &middot;
                                students only see the active ones.
                            </p>

                            <a href="{{ route(strtolower($office) . '.consultations.create') }}"
                               class="inline-flex items-center gap-1.5 w-fit
                                      px-3.5 py-2
                                      rounded-xl
                                      bg-blue-700
                                      text-white
                                      text-xs font-bold
                                      hover:bg-blue-800
                                      transition">

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>

                                Add service

                            </a>

                        </div>

                        @forelse ($services as $service)

                            <div class="group flex flex-col
                                        bg-white border border-gray-200
                                        rounded-2xl p-6
                                        hover:border-blue-300
                                        hover:shadow-lg
                                        transition duration-200">

                                <div class="flex items-start justify-between gap-3">

                                    <div class="w-12 h-12 rounded-xl bg-blue-50
                                                flex items-center justify-center">

                                        <svg class="w-6 h-6 text-blue-700"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M9 14h6m-6-4h6m2 11H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                                        </svg>

                                    </div>

                                    <span class="px-2.5 py-1
                                                 rounded-full
                                                 text-[9px]
                                                 font-extrabold
                                                 uppercase
                                                 {{ $service->is_active
                                                     ? 'bg-green-50 text-green-600'
                                                     : 'bg-gray-100 text-gray-500' }}">

                                        {{ $service->is_active ? 'Active' : 'Hidden' }}

                                    </span>

                                </div>

                                <h3 class="mt-5 text-base font-extrabold text-blue-950">
                                    {{ $service->name }}
                                </h3>

                                <p class="mt-2 text-xs leading-5 text-gray-500 flex-1">
                                    {{ $service->summary() }}
                                </p>

                                <div class="mt-5 pt-4
                                            border-t border-gray-100
                                            flex items-center gap-4
                                            text-xs font-bold">

                                    <a href="{{ route(strtolower($office) . '.consultations.edit', $service) }}"
                                       class="text-blue-700 hover:text-blue-900 transition">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route(strtolower($office) . '.consultations.destroy', $service) }}"
                                          data-confirm="Students will no longer see this service."
                                          data-confirm-title="Remove this service?"
                                          data-confirm-ok="Yes, remove it">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 transition">
                                            Remove
                                        </button>

                                    </form>

                                </div>

                            </div>

                        @empty

                            <div class="col-span-full px-6 py-14
                                        text-center text-sm text-gray-400">

                                No consultation services yet.

                                <a href="{{ route(strtolower($office) . '.consultations.create') }}"
                                   class="font-bold text-blue-700 hover:underline">
                                    Add your first service
                                </a>.

                            </div>

                        @endforelse

                    </div>

                </div>

            </div>


            {{-- =====================================================
                INFORMATION NOTE
            ====================================================== --}}
            <div class="mt-6
                        rounded-2xl
                        border border-blue-100
                        bg-blue-50
                        p-5">

                <div class="flex items-start gap-3">

                    <div class="w-9 h-9
                                rounded-xl
                                bg-blue-100
                                flex items-center
                                justify-center
                                shrink-0">

                        <svg class="w-4 h-4 text-blue-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>

                        </svg>

                    </div>

                    <div>

                        <p class="text-sm
                                  font-extrabold
                                  text-blue-950">

                            Consultation Information

                        </p>

                        <p class="mt-1
                                  text-xs
                                  leading-5
                                  text-blue-700">

                            Select a consultation service above to continue.
                            Appointment availability depends on the schedule
                            configured by the {{ $office }} Office.

                        </p>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                FOOTER
            ====================================================== --}}
            <div class="mt-7
                        flex flex-col
                        sm:flex-row
                        items-center
                        justify-between
                        gap-3
                        px-2">

                <div class="flex items-center gap-2">

                    <div class="w-8 h-8
                                rounded-xl
                                bg-blue-950
                                flex items-center
                                justify-center">

                        <span class="text-xs
                                     font-black
                                     text-yellow-400">

                            P

                        </span>

                    </div>

                    <div>

                        <p class="text-[9px]
                                  font-extrabold
                                  uppercase
                                  tracking-widest
                                  text-blue-950">

                            ISUFSTPASS

                        </p>

                        <p class="text-[9px]
                                  text-gray-400">

                            Secure • Reliable • Official

                        </p>

                    </div>

                </div>


                <p class="text-[10px]
                          text-gray-400">

                    {{ $office }} Consultation Services

                </p>

            </div>

        </div>

    </div>

</x-app-layout>

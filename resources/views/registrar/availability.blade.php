<x-app-layout>

    <div class="min-h-screen bg-[#f4f7fb] py-8">

        <div class="w-full px-4 sm:px-6 lg:px-8">

            {{-- =====================================================
                HEADER
            ====================================================== --}}
            <div class="mb-8">

                <div class="flex flex-col lg:flex-row
                            lg:items-center
                            lg:justify-between
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
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                </svg>

                            </div>

                            <div>

                                <p class="text-[10px]
                                          font-extrabold
                                          uppercase
                                          tracking-[0.18em]
                                          text-blue-600">

                                    Registrar's Office

                                </p>

                                <h1 class="text-2xl sm:text-3xl
                                           font-extrabold
                                           tracking-tight
                                           text-blue-950">

                                    Office Hours

                                </h1>

                            </div>

                        </div>


                        <p class="text-sm
                                  sm:text-base
                                  text-gray-500
                                  max-w-2xl">

                            Set the hours this office keeps. An approval may
                            only name a time inside these hours, so a student
                            is never asked to arrive when the office is shut.

                        </p>

                    </div>


                    {{-- STATUS --}}
                    <div class="inline-flex
                                items-center
                                gap-2
                                self-start
                                lg:self-center
                                px-4 py-2.5
                                rounded-2xl
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

                            Scheduling Active

                        </span>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                OFFICE HOURS

                The hours bound every approval: the approve popover
                only offers times inside this window.
            =====================================================
            --}}
            <x-office-hours />

        </div>

    </div>

</x-app-layout>

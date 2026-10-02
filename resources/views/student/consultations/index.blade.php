<x-app-layout>

    {{-- =========================================================
        CONSULTATION SERVICES — STUDENT CATALOGUE

        Every active service offered by the offices and departments.
        Picking one takes the student to the appointment booking form
        with the office and purpose already filled in.
    ========================================================== --}}

    <div class="min-h-screen bg-[#f6f8fc] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- PAGE HEADER --}}
            <div class="mb-8">

                <span class="inline-flex items-center gap-2
                             px-3 py-1.5
                             rounded-full
                             bg-purple-50
                             text-purple-700
                             text-[11px]
                             font-bold
                             uppercase
                             tracking-wide">

                    {{ $total }} {{ Str::plural('service', $total) }} available

                </span>

                <h1 class="mt-4 text-2xl sm:text-3xl
                           font-extrabold
                           text-[#102d5b]">

                    Consultation Services

                </h1>

                <p class="mt-2 text-sm text-gray-500 max-w-2xl">

                    Pick the type of consultation you need and we will
                    prepare your appointment form for you.

                </p>

            </div>


            {{-- OFFICE JUMP LINKS --}}
            @if ($services->isNotEmpty())

                <div class="mb-8 flex flex-wrap gap-2">

                    @foreach ($services->keys() as $office)

                        <a href="#office-{{ strtolower($office) }}"
                           class="px-3.5 py-2
                                  rounded-full
                                  border border-gray-200
                                  bg-white
                                  text-xs font-bold
                                  text-gray-600
                                  hover:border-blue-300
                                  hover:text-blue-700
                                  transition">

                            {{ $labels[$office] ?? $office }}

                        </a>

                    @endforeach

                </div>

            @endif


            @if ($services->isEmpty())

                {{-- EMPTY STATE --}}
                <div class="bg-white rounded-2xl
                            border border-gray-200
                            px-6 py-16
                            text-center">

                    <div class="mx-auto w-14 h-14 rounded-2xl
                                bg-gray-100
                                flex items-center justify-center">

                        <svg class="w-7 h-7 text-gray-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                        </svg>

                    </div>

                    <h2 class="mt-5 text-lg font-extrabold text-gray-900">
                        No consultation services yet
                    </h2>

                    <p class="mt-2 text-sm text-gray-500">
                        Please check back soon, or book an appointment and
                        describe your concern in the purpose field.
                    </p>

                    <a href="{{ route('student.appointments.create') }}"
                       class="mt-6 inline-flex items-center
                              px-5 py-2.5
                              rounded-xl
                              bg-blue-700
                              text-white
                              text-sm font-bold
                              hover:bg-blue-800
                              transition">

                        Book an appointment

                    </a>

                </div>

            @else

                {{-- SERVICE GROUPS --}}
                @foreach ($services as $office => $group)

                    <section id="office-{{ strtolower($office) }}"
                             class="mb-10 scroll-mt-6">

                        <div class="flex flex-col sm:flex-row
                                    sm:items-center sm:justify-between
                                    gap-3
                                    mb-4">

                            <div>

                                <h2 class="text-lg font-bold text-[#102d5b]">
                                    {{ $labels[$office] ?? $office }}
                                </h2>

                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $group->count() }}
                                    {{ Str::plural('service', $group->count()) }}
                                </p>

                            </div>

                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                            @foreach ($group as $service)

                                <a href="{{ route('student.appointments.create', [
                                    'office' => $office,
                                    'purpose' => $service->name,
                                ]) }}"
                                   class="group flex flex-col
                                          bg-white border border-gray-200
                                          rounded-2xl p-6
                                          hover:border-blue-300
                                          hover:shadow-lg
                                          transition duration-200">

                                    <div class="flex items-start justify-between gap-3">

                                        <div class="w-11 h-11 rounded-xl
                                                    bg-blue-50
                                                    flex items-center justify-center
                                                    shrink-0">

                                            <svg class="w-5 h-5 text-blue-700"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                            </svg>

                                        </div>

                                        <span class="text-[10px]
                                                      font-bold
                                                      uppercase
                                                      px-2 py-1
                                                      rounded-full
                                                      bg-green-50
                                                      text-green-700
                                                      shrink-0">

                                            Available

                                        </span>

                                    </div>

                                    <h3 class="mt-4 text-base font-bold text-gray-900">
                                        {{ $service->name }}
                                    </h3>

                                    <p class="mt-2 text-sm text-gray-500 leading-relaxed flex-1">
                                        {{ $service->summary() }}
                                    </p>

                                    <span class="mt-5 inline-flex items-center
                                                 text-sm font-semibold
                                                 text-blue-700">

                                        Request consultation

                                        <svg class="w-4 h-4 ml-2
                                                    group-hover:translate-x-1
                                                    transition"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 5l7 7-7 7"/>

                                        </svg>

                                    </span>

                                </a>

                            @endforeach

                        </div>

                    </section>

                @endforeach

            @endif

        </div>

    </div>

</x-app-layout>

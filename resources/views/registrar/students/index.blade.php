<x-app-layout>

    <div class="min-h-screen bg-[#f4f7fb] py-8">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="mb-8">

                <div class="flex flex-col sm:flex-row
                            sm:items-center sm:justify-between
                            gap-4">

                    <div class="flex items-center gap-3">

                        {{-- ICON --}}
                        <div class="w-12 h-12 rounded-2xl
                                    bg-blue-950
                                    flex items-center justify-center
                                    shadow-lg shadow-blue-950/20">

                            <svg class="w-6 h-6 text-yellow-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>

                            </svg>

                        </div>

                        <div>

                            <h1 class="text-2xl sm:text-3xl
                                       font-extrabold text-blue-950">

                                Student Record Lookup

                            </h1>

                            <p class="text-sm text-gray-500 mt-1">

                                Search student profiles and review
                                their transaction history.

                            </p>

                        </div>

                    </div>


                    {{-- RECORD COUNT --}}
                    <div class="inline-flex items-center gap-2
                                px-4 py-2.5
                                bg-white
                                border border-blue-100
                                rounded-full
                                shadow-sm">

                        <span class="w-2.5 h-2.5
                                     rounded-full
                                     bg-green-500">
                        </span>

                        <span class="text-xs font-bold
                                     text-gray-600">

                            Student Records

                        </span>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                SEARCH CARD
            ========================================================== --}}
            <div class="relative overflow-hidden
                        bg-gradient-to-br
                        from-blue-950
                        via-blue-900
                        to-indigo-950
                        rounded-3xl
                        shadow-xl
                        shadow-blue-950/15
                        mb-7">

                {{-- Decorative elements --}}
                <div class="absolute
                            -top-24 -right-24
                            w-72 h-72
                            rounded-full
                            bg-blue-800/30">
                </div>

                <div class="absolute
                            -bottom-32 -left-20
                            w-80 h-80
                            rounded-full
                            bg-indigo-800/20">
                </div>


                <div class="relative p-6 sm:p-8">

                    <div class="flex items-start gap-4 mb-6">

                        <div class="w-11 h-11
                                    rounded-xl
                                    bg-yellow-400
                                    flex items-center
                                    justify-center
                                    shrink-0">

                            <svg class="w-5 h-5 text-blue-950"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>

                            </svg>

                        </div>

                        <div>

                            <h2 class="text-lg font-extrabold
                                       text-white">

                                Find Student Record

                            </h2>

                            <p class="text-sm text-blue-200 mt-1">

                                Search using the student's name,
                                email, course, or contact number.

                            </p>

                        </div>

                    </div>


                    <form method="GET"
                          action="{{ route('registrar.students.index') }}">

                        <div class="flex flex-col lg:flex-row gap-3">

                            {{-- SEARCH INPUT --}}
                            <div class="relative flex-1">

                                <svg class="absolute left-4 top-1/2
                                            -translate-y-1/2
                                            w-5 h-5 text-gray-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>

                                </svg>

                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Name, email, course, or contact number"
                                    class="w-full
                                           pl-12 pr-4
                                           py-3.5
                                           rounded-2xl
                                           border-0
                                           bg-white
                                           text-sm
                                           text-gray-800
                                           shadow-lg
                                           focus:outline-none
                                           focus:ring-4
                                           focus:ring-yellow-400/30"
                                >

                            </div>


                            {{-- SEARCH --}}
                            <button type="submit"
                                    class="inline-flex
                                           items-center
                                           justify-center
                                           gap-2
                                           px-7 py-3.5
                                           rounded-2xl
                                           bg-yellow-400
                                           text-blue-950
                                           text-sm
                                           font-extrabold
                                           shadow-lg
                                           hover:bg-yellow-300
                                           transition">

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>

                                </svg>

                                Search

                            </button>


                            {{-- RESET --}}
                            @if ($search)

                                <a href="{{ route('registrar.students.index') }}"
                                   class="inline-flex
                                          items-center
                                          justify-center
                                          gap-2
                                          px-6 py-3.5
                                          rounded-2xl
                                          border border-white/20
                                          bg-white/10
                                          text-white
                                          text-sm
                                          font-bold
                                          hover:bg-white/20
                                          transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12"/>

                                    </svg>

                                    Reset

                                </a>

                            @endif

                        </div>


                        <div class="flex items-center gap-2 mt-4">

                            <svg class="w-4 h-4 text-blue-300"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-8h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>

                            </svg>

                            <p class="text-xs text-blue-200">

                                Enter at least part of a student's
                                name, email, course, or contact number.

                            </p>

                        </div>

                    </form>

                </div>

            </div>


            {{-- =========================================================
                SEARCH RESULT HEADER
            ========================================================== --}}
            <div class="flex flex-col sm:flex-row
                        sm:items-center sm:justify-between
                        gap-3 mb-4">

                <div>

                    <p class="text-xs font-extrabold
                              uppercase tracking-widest
                              text-gray-400">

                        {{ $search ? 'Search Results' : 'Student Directory' }}

                    </p>

                    @if ($search)

                        <p class="text-sm text-gray-500 mt-1">

                            Showing results for
                            <span class="font-bold text-blue-800">
                                "{{ $search }}"
                            </span>

                        </p>

                    @else

                        <p class="text-sm text-gray-500 mt-1">
                            Registered students in ISUFSTPASS
                        </p>

                    @endif

                </div>


                @if ($students->total() > 0)

                    <div class="inline-flex items-center
                                gap-2
                                px-3.5 py-2
                                bg-white
                                border border-gray-100
                                rounded-xl
                                shadow-sm">

                        <span class="text-xs font-bold text-gray-400">
                            TOTAL
                        </span>

                        <span class="text-sm font-extrabold
                                     text-blue-950">

                            {{ $students->total() }}

                        </span>

                    </div>

                @endif

            </div>


            {{-- =========================================================
                STUDENT CARDS
            ========================================================== --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">

                @forelse ($students as $student)

                    <a href="{{ route('registrar.students.show', $student) }}"
                       class="group relative
                              bg-white
                              rounded-3xl
                              border border-gray-100
                              shadow-sm
                              overflow-hidden
                              hover:border-blue-200
                              hover:shadow-xl
                              hover:shadow-blue-950/10
                              hover:-translate-y-0.5
                              transition-all duration-200">


                        {{-- TOP ACCENT --}}
                        <div class="h-1.5
                                    bg-gradient-to-r
                                    from-blue-950
                                    via-blue-700
                                    to-yellow-400">
                        </div>


                        <div class="p-5">

                            {{-- Student Header --}}
                            <div class="flex items-center gap-4">

                                {{-- Avatar --}}
                                <div class="relative shrink-0">

                                    <div class="w-14 h-14
                                                rounded-2xl
                                                bg-blue-950
                                                overflow-hidden
                                                flex items-center
                                                justify-center
                                                text-yellow-400
                                                font-extrabold
                                                text-lg
                                                uppercase
                                                shadow-md">

                                        @if ($student->studentProfile?->avatar)

                                            <img
                                                src="{{ $student->studentProfile->avatar_url }}"
                                                alt="Avatar"
                                                class="w-full h-full object-cover">

                                        @else

                                            {{ substr($student->name, 0, 1) }}

                                        @endif

                                    </div>


                                    {{-- Verified --}}
                                    <div class="absolute
                                                -right-1.5
                                                -bottom-1.5
                                                w-6 h-6
                                                rounded-full
                                                bg-green-500
                                                border-2
                                                border-white
                                                flex items-center
                                                justify-center">

                                        <svg class="w-3 h-3 text-white"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="3"
                                                  d="M5 13l4 4L19 7"/>

                                        </svg>

                                    </div>

                                </div>


                                {{-- Student Name --}}
                                <div class="min-w-0 flex-1">

                                    <div class="flex items-center
                                                gap-2">

                                        <p class="font-extrabold
                                                  text-blue-950
                                                  text-sm
                                                  truncate
                                                  group-hover:text-blue-700
                                                  transition">

                                            {{ $student->name }}

                                        </p>

                                    </div>

                                    <p class="text-xs
                                              text-gray-400
                                              truncate
                                              mt-1">

                                        {{ $student->email }}

                                    </p>

                                </div>

                            </div>


                            {{-- STUDENT INFORMATION --}}
                            <div class="mt-5 space-y-2">

                                {{-- Course --}}
                                <div class="flex items-center gap-3
                                            rounded-xl
                                            bg-[#f4f7fb]
                                            border border-gray-100
                                            px-3 py-2.5">

                                    <div class="w-8 h-8
                                                rounded-lg
                                                bg-blue-50
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
                                                  d="M12 14l9-5-9-5-9 5 9 5z"/>

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M5 12v5c3 2 7 3 7 3s4-1 7-3v-5"/>

                                        </svg>

                                    </div>

                                    <div class="min-w-0">

                                        <p class="text-[9px]
                                                  uppercase
                                                  tracking-widest
                                                  font-bold
                                                  text-gray-400">

                                            Course

                                        </p>

                                        <p class="text-xs
                                                  font-bold
                                                  text-gray-700
                                                  truncate">

                                            {{ $student->studentProfile?->course ?: 'No course set' }}

                                        </p>

                                    </div>

                                </div>


                                {{-- Year Level --}}
                                @if ($student->studentProfile?->year_level)

                                    <div class="flex items-center gap-3
                                                rounded-xl
                                                bg-[#f4f7fb]
                                                border border-gray-100
                                                px-3 py-2.5">

                                        <div class="w-8 h-8
                                                    rounded-lg
                                                    bg-yellow-50
                                                    flex items-center
                                                    justify-center
                                                    shrink-0">

                                            <svg class="w-4 h-4 text-yellow-600"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M12 6v6l4 2"/>

                                                <circle cx="12"
                                                        cy="12"
                                                        r="9"
                                                        stroke-width="2"/>

                                            </svg>

                                        </div>

                                        <div>

                                            <p class="text-[9px]
                                                      uppercase
                                                      tracking-widest
                                                      font-bold
                                                      text-gray-400">

                                                Year Level

                                            </p>

                                            <p class="text-xs
                                                      font-bold
                                                      text-gray-700">

                                                Year {{ $student->studentProfile->year_level }}

                                            </p>

                                        </div>

                                    </div>

                                @endif

                            </div>


                            {{-- VIEW RECORD --}}
                            <div class="mt-5
                                        pt-4
                                        border-t border-gray-100
                                        flex items-center
                                        justify-between">

                                <span class="text-xs
                                             font-extrabold
                                             text-blue-700
                                             group-hover:text-blue-900
                                             transition">

                                    View Student Record

                                </span>

                                <div class="w-8 h-8
                                            rounded-xl
                                            bg-blue-50
                                            flex items-center
                                            justify-center
                                            group-hover:bg-blue-900
                                            transition">

                                    <svg class="w-4 h-4
                                                text-blue-700
                                                group-hover:text-yellow-400
                                                transition"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M13 7l5 5m0 0l-5 5m5-5H6"/>

                                    </svg>

                                </div>

                            </div>

                        </div>

                    </a>

                @empty

                    {{-- =================================================
                        EMPTY STATE
                    ================================================== --}}
                    <div class="sm:col-span-2
                                lg:col-span-3
                                bg-white
                                rounded-3xl
                                border border-gray-100
                                shadow-sm
                                p-12 sm:p-16
                                text-center">

                        <div class="mx-auto
                                    w-20 h-20
                                    rounded-3xl
                                    bg-blue-50
                                    flex items-center
                                    justify-center">

                            <svg class="w-10 h-10 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>

                            </svg>

                        </div>


                        <h2 class="mt-6
                                   text-lg
                                   font-extrabold
                                   text-blue-950">

                            {{ $search
                                ? 'No Students Found'
                                : 'No Student Records Yet' }}

                        </h2>


                        <p class="text-sm
                                  text-gray-500
                                  mt-2
                                  max-w-md
                                  mx-auto">

                            {{ $search
                                ? 'No students match your search. Try a different name, course, email, or contact number.'
                                : 'Registered student accounts will appear here.' }}

                        </p>


                        @if ($search)

                            <a href="{{ route('registrar.students.index') }}"
                               class="inline-flex
                                      items-center
                                      gap-2
                                      mt-6
                                      px-5 py-2.5
                                      rounded-xl
                                      bg-blue-900
                                      text-white
                                      text-sm
                                      font-bold
                                      hover:bg-blue-950
                                      transition">

                                Clear Search

                            </a>

                        @endif

                    </div>

                @endforelse

            </div>


            {{-- =========================================================
                PAGINATION
            ========================================================== --}}
            @if ($students->hasPages())

                <div class="mt-8
                            bg-white
                            rounded-2xl
                            border border-gray-100
                            shadow-sm
                            px-5 py-4">

                    {{ $students->links() }}

                </div>

            @endif


            {{-- =========================================================
                FOOTER
            ========================================================== --}}
            <div class="mt-8
                        flex flex-col sm:flex-row
                        items-center
                        justify-between
                        gap-3
                        px-2">

                <div class="flex items-center gap-2">

                    <div class="w-7 h-7
                                rounded-lg
                                bg-blue-950
                                flex items-center
                                justify-center">

                        <span class="text-yellow-400
                                     text-xs font-black">

                            P

                        </span>

                    </div>

                    <div>

                        <p class="text-[10px]
                                  font-extrabold
                                  uppercase
                                  tracking-widest
                                  text-blue-950">

                            ISUFSTPASS

                        </p>

                        <p class="text-[9px] text-gray-400">

                            Secure • Reliable • Official

                        </p>

                    </div>

                </div>


                <p class="text-[10px] text-gray-400">

                    Registrar Student Records

                </p>

            </div>

        </div>

    </div>

</x-app-layout>
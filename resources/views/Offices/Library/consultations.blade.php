<x-app-layout>

    <div class="min-h-screen bg-[#f4f7fb] py-10">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= HEADER ================= --}}
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-11 h-11 rounded-xl bg-blue-900 flex items-center justify-center shadow-sm">
                        <svg class="w-6 h-6 text-white"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5S19.832 5.477 21 6.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253" />
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-2xl font-extrabold text-gray-900">
                            {{ $office }} Consultation Services
                        </h1>

                        <p class="text-sm text-gray-500 mt-1">
                            Select a library consultation service that you need.
                        </p>
                    </div>
                </div>
            </div>


            {{-- ================= SERVICES ================= --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Library Consultation Services
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Choose a service to request an appointment with the Library.
                        </p>
                    </div>

                    <span class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-full
                                 bg-blue-50 text-blue-700 text-xs font-semibold">
                        Library Office
                    </span>
                </div>


                {{-- SERVICE GRID --}}
                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">


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


            {{-- ================= FOOTER INFO ================= --}}
            <div class="mt-6 bg-blue-950 rounded-2xl p-5 text-white">

                <div class="flex flex-col sm:flex-row sm:items-center
                            sm:justify-between gap-3">

                    <div>
                        <p class="font-bold">
                            ISUFSTPASS Library Consultation
                        </p>

                        <p class="text-xs text-blue-200 mt-1">
                            Schedule your consultation and receive your
                            appointment reference through ISUFSTPASS.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-blue-200">
                        <span class="w-2 h-2 rounded-full bg-green-400"></span>
                        Library Services
                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>

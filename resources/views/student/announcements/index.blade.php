<x-app-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Announcements</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Official updates from the campus offices.
                    </p>
                </div>

                @if ($announcements->total() > 0)
                    <span class="inline-flex items-center gap-2 text-xs font-bold text-gray-500">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        {{ $announcements->total() }}
                        {{ \Illuminate\Support\Str::plural('announcement', $announcements->total()) }}
                    </span>
                @endif
            </div>


            <div class="mt-6 space-y-3">

                @forelse ($announcements as $announcement)

                    <article class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                                    hover:border-blue-200 transition">

                        <div class="flex flex-wrap items-center gap-2">

                            @if ($announcement->office)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg
                                             bg-blue-50 text-blue-700 text-[10px] font-bold
                                             uppercase tracking-wide">
                                    {{ $announcement->office }}
                                </span>
                            @endif

                            @if ($announcement->published_at)
                                <span class="text-xs text-gray-400">
                                    {{ $announcement->published_at->format('M d, Y') }}
                                </span>
                            @endif

                        </div>


                        <h2 class="mt-2 text-base font-black text-gray-900">
                            <a href="{{ route('student.announcements.show', $announcement) }}"
                               class="hover:text-blue-700 transition">
                                {{ $announcement->title }}
                            </a>
                        </h2>


                        <p class="mt-1 text-sm text-gray-500 leading-relaxed">
                            {{ \Illuminate\Support\Str::limit($announcement->body, 180) }}
                        </p>


                        <a href="{{ route('student.announcements.show', $announcement) }}"
                           class="mt-3 inline-flex items-center gap-1 text-xs font-bold
                                  text-blue-700 hover:text-blue-900 transition">

                            Read more

                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" d="M9 5l7 7-7 7"/>

                            </svg>

                        </a>

                    </article>

                @empty

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm
                                px-6 py-14 text-center">

                        <div class="mx-auto w-14 h-14 rounded-2xl bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-7 h-7 text-blue-600" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>

                            </svg>

                        </div>

                        <h2 class="mt-4 text-sm font-black text-gray-800">
                            No announcements yet
                        </h2>

                        <p class="mt-1 text-xs text-gray-400">
                            Campus updates will appear here once an office posts one.
                        </p>

                    </div>

                @endforelse

            </div>


            @if ($announcements->hasPages())
                <div class="mt-6">
                    {{ $announcements->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

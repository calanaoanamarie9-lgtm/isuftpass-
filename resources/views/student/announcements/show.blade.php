<x-app-layout>
    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <a href="{{ route('student.announcements.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500
                      hover:text-blue-700 transition">

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>

                All announcements

            </a>


            <article class="mt-4 bg-white rounded-2xl border border-gray-100 shadow-sm
                            p-6 sm:p-8">

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
                            {{ $announcement->published_at->format('F j, Y') }}
                        </span>
                    @endif

                </div>


                <h1 class="mt-3 text-2xl font-black text-gray-900 leading-snug">
                    {{ $announcement->title }}
                </h1>


                <div class="mt-4 text-sm text-gray-700 leading-relaxed">
                    {!! nl2br(e($announcement->body)) !!}
                </div>


                <div class="mt-6 pt-4 border-t border-gray-100 text-xs text-gray-400">
                    Posted by {{ $announcement->author?->name ?? 'the administration' }}
                </div>

            </article>


            <a href="{{ route('student.announcements.index') }}"
               class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold
                      text-blue-700 hover:text-blue-900 transition">

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>

                Back to announcements

            </a>

        </div>
    </div>
</x-app-layout>

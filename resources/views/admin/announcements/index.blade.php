<x-app-layout>

    <div class="min-h-screen bg-[#f5f7fb] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">

                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-1">
                        Administration
                    </p>

                    <h1 class="text-2xl sm:text-3xl font-black text-[#12376b]">
                        System Notifications
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Create and manage announcements displayed to students.
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 bg-white border border-gray-200
                            rounded-xl px-4 py-2.5 shadow-sm w-fit">

                    <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>

                    <span class="text-xs font-bold text-gray-700">
                        Broadcast System Active
                    </span>

                </div>

            </div>


            {{-- =========================================================
                SUCCESS MESSAGE
            ========================================================== --}}
            @if (session('status'))

                <div class="mb-5 flex items-start gap-3 rounded-xl
                            bg-green-50 border border-green-200 px-4 py-3">

                    <div class="w-7 h-7 rounded-lg bg-green-100
                                flex items-center justify-center shrink-0">

                        <svg class="w-4 h-4 text-green-600"
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
                            Success
                        </p>

                        <p class="text-xs text-green-700 mt-0.5">
                            {{ session('status') }}
                        </p>
                    </div>

                </div>

            @endif


            {{-- =========================================================
                ERROR MESSAGE
            ========================================================== --}}
            @if ($errors->any())

                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3">

                    <div class="flex items-start gap-3">

                        <div class="w-7 h-7 rounded-lg bg-red-100
                                    flex items-center justify-center shrink-0">

                            <svg class="w-4 h-4 text-red-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 9v4m0 4h.01M5.07 19h13.86a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16a2 2 0 001.73 3z"/>

                            </svg>

                        </div>

                        <div>

                            <p class="text-sm font-bold text-red-800">
                                Please check the following:
                            </p>

                            <ul class="mt-1 text-xs text-red-700 list-disc list-inside space-y-0.5">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                CREATE BROADCAST
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200
                        shadow-sm overflow-hidden mb-6">

                {{-- Section Header --}}
                <div class="px-6 py-5 border-b border-gray-100">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-xl bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-5 h-5 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>

                            </svg>

                        </div>

                        <div>

                            <h2 class="text-base font-black text-[#12376b]">
                                Create Announcement
                            </h2>

                            <p class="text-xs text-gray-500 mt-0.5">
                                Publish an announcement that will appear on student dashboards.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Form --}}
                <form method="POST"
                      action="{{ route('admin.announcements.store') }}"
                      class="p-6">

                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                        {{-- Title --}}
                        <div class="lg:col-span-4">

                            <label class="block text-xs font-bold text-gray-600 mb-2">
                                Announcement Title
                            </label>

                            <input type="text"
                                   name="title"
                                   required
                                   placeholder="e.g. Schedule of Enrollment Week"
                                   class="w-full rounded-xl border-gray-300
                                          focus:border-blue-500 focus:ring-blue-500
                                          text-sm px-4 py-2.5">

                        </div>


                        {{-- Office --}}
                        <div class="lg:col-span-3">

                            <label class="block text-xs font-bold text-gray-600 mb-2">
                                Office
                            </label>

                            <select name="office"
                                    required
                                    class="w-full rounded-xl border-gray-300
                                           focus:border-blue-500 focus:ring-blue-500
                                           text-sm px-4 py-2.5">

                                @foreach ($offices as $value => $label)

                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Message --}}
                        <div class="lg:col-span-5">

                            <label class="block text-xs font-bold text-gray-600 mb-2">
                                Message
                            </label>

                            <input type="text"
                                   name="body"
                                   required
                                   placeholder="Write the announcement details..."
                                   class="w-full rounded-xl border-gray-300
                                          focus:border-blue-500 focus:ring-blue-500
                                          text-sm px-4 py-2.5">

                        </div>

                    </div>


                    {{-- Publish Button --}}
                    <div class="mt-5 flex justify-end">

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2
                                       px-5 py-2.5 rounded-xl
                                       bg-[#063b91] hover:bg-[#052f75]
                                       text-white text-sm font-bold
                                       shadow-sm transition">

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 19V5m0 0l-6 6m6-6l6 6"/>

                            </svg>

                            Publish Announcement

                        </button>

                    </div>

                </form>

            </div>


            {{-- =========================================================
                SEARCH
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200
                        shadow-sm p-5 mb-6">

                <form method="GET"
                      action="{{ route('admin.announcements.index') }}"
                      class="flex flex-col sm:flex-row gap-3">

                    <div class="flex-1">

                        <label class="block text-xs font-bold text-gray-600 mb-2">
                            Search Announcements
                        </label>

                        <div class="relative">

                            <svg class="absolute left-3 top-1/2 -translate-y-1/2
                                        w-4 h-4 text-gray-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>

                            </svg>

                            <input type="text"
                                   name="q"
                                   value="{{ $search }}"
                                   placeholder="Search by title or message..."
                                   class="w-full pl-10 pr-4 py-2.5
                                          rounded-xl border-gray-300
                                          focus:border-blue-500 focus:ring-blue-500
                                          text-sm">

                        </div>

                    </div>


                    <div class="flex flex-wrap items-end gap-2">

                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl
                                       bg-gray-900 hover:bg-gray-800
                                       text-white text-sm font-bold transition">

                            Search

                        </button>

                        @if ($search)

                            <a href="{{ route('admin.announcements.index') }}"
                               class="px-5 py-2.5 rounded-xl
                                      bg-gray-100 hover:bg-gray-200
                                      text-gray-700 text-sm font-bold transition">

                                Reset

                            </a>

                        @endif

                    </div>

                </form>

            </div>


            {{-- =========================================================
                ANNOUNCEMENTS LIST
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200
                        shadow-sm overflow-hidden">

                {{-- List Header --}}
                <div class="px-6 py-5 border-b border-gray-100">

                    <div class="flex items-center justify-between gap-4">

                        <div>

                            <h2 class="text-base font-black text-[#12376b]">
                                Published Announcements
                            </h2>

                            <p class="text-xs text-gray-500 mt-0.5">
                                Manage system-wide student notifications.
                            </p>

                        </div>

                        <div class="hidden sm:flex items-center gap-2
                                    text-xs font-semibold text-gray-400">

                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Live Broadcasts

                        </div>

                    </div>

                </div>


                {{-- List --}}
                <div>

                    @forelse ($announcements as $announcement)

                        <div class="px-6 py-5 border-b border-gray-100
                                    last:border-0 hover:bg-gray-50/70 transition">

                            <div class="flex flex-col lg:flex-row
                                        lg:items-center gap-5">

                                {{-- Announcement Information --}}
                                <div class="flex-1 min-w-0">

                                    <div class="flex items-center gap-2 flex-wrap">

                                        {{-- Office --}}
                                        <span class="inline-flex items-center
                                                     px-2.5 py-1 rounded-lg
                                                     bg-blue-50 text-blue-700
                                                     text-[10px] font-bold uppercase
                                                     tracking-wide">

                                            {{ $announcement->office }}

                                        </span>


                                        {{-- Status --}}
                                        @if ($announcement->is_published)

                                            <span class="inline-flex items-center gap-1.5
                                                         px-2.5 py-1 rounded-lg
                                                         bg-green-50 text-green-700
                                                         text-[10px] font-bold">

                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>

                                                Published

                                            </span>

                                        @else

                                            <span class="inline-flex items-center gap-1.5
                                                         px-2.5 py-1 rounded-lg
                                                         bg-gray-100 text-gray-500
                                                         text-[10px] font-bold">

                                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>

                                                Hidden

                                            </span>

                                        @endif

                                    </div>


                                    {{-- Title --}}
                                    <h3 class="mt-2 text-sm sm:text-base
                                               font-black text-gray-900">

                                        {{ $announcement->title }}

                                    </h3>


                                    {{-- Message --}}
                                    <p class="mt-1 text-sm text-gray-500
                                              leading-relaxed">

                                        {{ $announcement->body }}

                                    </p>


                                    {{-- Author / Date --}}
                                    <div class="mt-2 flex items-center gap-2
                                                text-xs text-gray-400">

                                        <span>
                                            {{ $announcement->author?->name ?? 'System' }}
                                        </span>

                                        <span>•</span>

                                        @if ($announcement->published_at)

                                            <span>
                                                {{ $announcement->published_at->format('M d, Y h:i A') }}
                                            </span>

                                        @else

                                            <span>
                                                Not scheduled
                                            </span>

                                        @endif

                                    </div>

                                </div>


                                {{-- Actions --}}
                                <div class="flex items-center gap-2 lg:shrink-0">

                                    {{-- Publish / Unpublish --}}
                                    <form method="POST"
                                          action="{{ route('admin.announcements.toggle', $announcement) }}"
                                          data-confirm="{{ $announcement->is_published
                                            ? 'This announcement will no longer be shown to students.'
                                            : 'This announcement will be shown to students immediately.' }}"
                                          data-confirm-title="{{ $announcement->is_published
                                            ? 'Unpublish this broadcast?'
                                            : 'Publish this broadcast?' }}"
                                          data-confirm-ok="{{ $announcement->is_published
                                            ? 'Yes, unpublish'
                                            : 'Yes, publish' }}"
                                          data-confirm-icon="question">

                                        @csrf
                                        @method('PUT')

                                        <button type="submit"
                                                class="inline-flex items-center gap-2
                                                       px-3.5 py-2 rounded-lg
                                                       text-xs font-bold transition
                                                       {{ $announcement->is_published
                                                            ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border border-yellow-200'
                                                            : 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-200' }}">

                                            @if ($announcement->is_published)

                                                <svg class="w-3.5 h-3.5"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.303-3.978M6.228 6.228A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.563 2.845M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>

                                                </svg>

                                                Unpublish

                                            @else

                                                <svg class="w-3.5 h-3.5"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M5 13l4 4L19 7"/>

                                                </svg>

                                                Publish

                                            @endif

                                        </button>

                                    </form>


                                    {{-- Delete --}}
                                    <form method="POST"
                                          action="{{ route('admin.announcements.destroy', $announcement) }}"
                                          data-confirm="This broadcast will be permanently removed."
                                          data-confirm-title="Delete this broadcast?"
                                          data-confirm-ok="Yes, delete it">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex items-center gap-2
                                                       px-3.5 py-2 rounded-lg
                                                       bg-red-50 hover:bg-red-100
                                                       border border-red-100
                                                       text-red-600
                                                       text-xs font-bold transition">

                                            <svg class="w-3.5 h-3.5"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/>

                                            </svg>

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    @empty

                        {{-- Empty State --}}
                        <div class="px-6 py-16 text-center">

                            <div class="mx-auto w-14 h-14 rounded-2xl
                                        bg-blue-50 flex items-center justify-center">

                                <svg class="w-7 h-7 text-blue-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1"/>

                                </svg>

                            </div>

                            <h3 class="mt-4 text-sm font-black text-gray-800">

                                {{ $search
                                    ? 'No announcements found'
                                    : 'No announcements yet' }}

                            </h3>

                            <p class="mt-1 text-xs text-gray-400">

                                {{ $search
                                    ? 'Try using a different search keyword.'
                                    : 'Create your first announcement above to notify students.' }}

                            </p>

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- Pagination --}}
            @if ($announcements->hasPages())

                <div class="mt-6">
                    {{ $announcements->links() }}
                </div>

            @endif


            {{-- =========================================================
                FOOTER NOTICE
            ========================================================== --}}
            <div class="mt-6 bg-blue-50 border border-blue-100
                        rounded-xl px-5 py-4">

                <div class="flex items-start gap-3">

                    <div class="w-8 h-8 rounded-lg bg-blue-600
                                flex items-center justify-center shrink-0">

                        <svg class="w-4 h-4 text-white"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>

                        </svg>

                    </div>

                    <div>

                        <p class="text-xs font-bold text-blue-900">
                            About System Broadcasts
                        </p>

                        <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                            Published announcements are displayed to students through the ISUFSTPASS
                            notification system. Use broadcasts only for official university information.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
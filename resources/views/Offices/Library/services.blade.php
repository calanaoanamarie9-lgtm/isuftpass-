<x-app-layout>

    @php
        // The page owns the drawings; the row only stores which one to use.
        $icons = [
            'computer' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z',
            'megaphone' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46',
            'letter' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
            'play' => 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z',
            'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
            'copy' => 'M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75',
            'list' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
            'scan' => 'M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'book' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        ];

        $chips = [
            'research' => 'bg-blue-50 text-blue-700',
            'access' => 'bg-emerald-50 text-emerald-700',
            'awareness' => 'bg-amber-50 text-amber-700',
            'reproduction' => 'bg-violet-50 text-violet-700',
        ];
    @endphp

    <div class="min-h-screen bg-[#f4f7fb] py-10"
         x-data="libraryServices(@js($cards), @js($icons))">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= HEADER ================= --}}
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-900 flex items-center justify-center shadow-sm">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5S19.832 5.477 21 6.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253" />
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-2xl font-extrabold text-gray-900">
                            Other Library Services
                        </h1>

                        <p class="text-sm text-gray-500 mt-1">
                            Beyond consultations — computers, referrals, scans and the rest of the library's services.
                        </p>
                    </div>
                </div>

                <span class="hidden sm:inline-flex items-center gap-2 self-start sm:self-auto
                             px-3 py-1.5 rounded-full bg-white border border-gray-100 shadow-sm
                             text-xs font-semibold text-gray-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $services->count() }} {{ Str::plural('service', $services->count()) }}
                </span>
            </div>


            {{-- ================= TOOLBAR ================= --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 mb-6">

                <div class="flex flex-col lg:flex-row lg:items-center gap-4">

                    <div class="relative flex-1">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                        </svg>

                        <input type="search"
                               x-model="search"
                               placeholder="Search a service, a title or a keyword"
                               aria-label="Search library services"
                               class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-gray-200 bg-gray-50
                                      text-sm text-gray-800 placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                      focus:bg-white transition">

                        <button type="button"
                                x-show="search !== ''"
                                x-cloak
                                @click="search = ''"
                                aria-label="Clear search"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-lg
                                       flex items-center justify-center text-gray-400 hover:text-gray-600
                                       hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                                @click="category = 'all'"
                                :class="category === 'all'
                                    ? 'bg-blue-700 text-white border-blue-700'
                                    : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-800'"
                                class="px-3.5 py-1.5 rounded-full border text-xs font-bold transition">
                            All
                        </button>

                        @foreach ($categories as $key => $label)
                            <button type="button"
                                    @click="category = @js($key)"
                                    :class="category === @js($key)
                                        ? 'bg-blue-700 text-white border-blue-700'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-800'"
                                    class="px-3.5 py-1.5 rounded-full border text-xs font-bold transition">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                </div>

                <p class="mt-3 text-xs text-gray-400">
                    Showing <span class="font-bold text-gray-600" x-text="filtered.length"></span>
                    of {{ $services->count() }} {{ Str::plural('service', $services->count()) }}
                </p>
            </div>


            {{-- ================= SERVICE GRID ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">

                @foreach ($services as $service)
                    <div x-show="visible(@js($service->slug))"
                         class="group flex flex-col bg-white border border-gray-200 rounded-2xl p-6
                                hover:border-blue-300 hover:shadow-lg transition duration-200">

                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-800 flex items-center justify-center
                                        group-hover:bg-blue-700 group-hover:text-white transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="{{ $icons[$service->icon] ?? $icons['book'] }}" />
                                </svg>
                            </div>

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold
                                         {{ $chips[$service->category] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $service->categoryLabel() }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-gray-900">
                            {{ $service->name }}
                        </h3>

                        <p class="text-sm text-gray-500 leading-relaxed mt-2">
                            {{ $service->summary }}
                        </p>

                        <div class="mt-auto pt-5">
                            <button type="button"
                                    @click="openService(@js($service->slug))"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl
                                           text-xs font-bold transition
                                           {{ $service->is_requestable
                                                ? 'bg-blue-700 text-white hover:bg-blue-800'
                                                : 'bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
                                {{ $service->is_requestable ? 'Request this service' : 'How to avail' }}

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach


                {{-- Shown when nothing matches the search / category --}}
                <div x-show="filtered.length === 0"
                     x-cloak
                     class="col-span-full bg-white border border-dashed border-gray-300 rounded-2xl px-6 py-14 text-center">

                    <div class="w-12 h-12 mx-auto rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-base font-bold text-gray-900">
                        No service matches that search
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Try a different word, or show every service again.
                    </p>

                    <button type="button"
                            @click="search = ''; category = 'all'"
                            class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl
                                   bg-blue-50 text-blue-800 text-xs font-bold hover:bg-blue-100 transition">
                        Clear filters
                    </button>
                </div>

            </div>


            {{-- ================= FOOTNOTE ================= --}}
            <div class="mt-8 bg-white border border-gray-100 rounded-2xl p-6
                        flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>
                    <h2 class="text-base font-bold text-gray-900">
                        Need a one-on-one session instead?
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Book a consultation with a librarian for research, reference or online database help.
                    </p>
                </div>

                <a href="{{ route('library.consultations') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl
                          bg-blue-700 text-white text-xs font-bold hover:bg-blue-800 transition shrink-0">
                    Consultation services
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

        </div>


        {{-- ================= DETAIL PANEL ================= --}}
        <div x-show="active"
             x-cloak
             @keydown.escape.window="close()"
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true"
             aria-labelledby="service-panel-title">

            <div class="flex min-h-full items-end sm:items-center justify-center p-4">

                <div x-show="active"
                     x-transition.opacity
                     @click="close()"
                     class="fixed inset-0 bg-gray-900/50"></div>

                <div x-show="active"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                     class="relative w-full sm:max-w-xl bg-white rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden">

                    {{-- Panel header --}}
                    <div class="px-6 pt-6 pb-5 border-b border-gray-100">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-800 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              :d="activeIcon"></path>
                                    </svg>
                                </div>

                                <div>
                                    <h2 id="service-panel-title" class="text-lg font-extrabold text-gray-900" x-text="active?.name"></h2>
                                    <p class="text-xs font-semibold text-gray-400 mt-0.5" x-text="active?.categoryLabel"></p>
                                </div>
                            </div>

                            <button type="button"
                                    @click="close()"
                                    aria-label="Close"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center
                                           text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Panel body --}}
                    <div class="px-6 py-5 space-y-5 max-h-[60vh] overflow-y-auto">

                        <p class="text-sm text-gray-600 leading-relaxed" x-text="active?.description"></p>

                        <div x-show="active?.howToAvail">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                How to avail
                            </h3>
                            <p class="text-sm text-gray-600 leading-relaxed mt-2" x-text="active?.howToAvail"></p>
                        </div>

                        <div x-show="active?.requirements?.length">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                What to prepare
                            </h3>

                            <ul class="mt-2 space-y-1.5">
                                <template x-for="item in (active?.requirements ?? [])" :key="item">
                                    <li class="flex items-start gap-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4 mt-0.5 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        <span x-text="item"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                    </div>

                    {{-- Panel footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100
                                flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                        <div class="text-xs text-gray-400">
                            <template x-if="active?.requestable">
                                <span>Send these details to the library, or hand them in at the counter.</span>
                            </template>

                            <template x-if="active && ! active.requestable">
                                <span>No request needed — walk in during library hours.</span>
                            </template>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button"
                                    x-show="active?.requestable"
                                    @click="copyDetails()"
                                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl
                                           border border-gray-200 bg-white text-xs font-bold text-gray-600
                                           hover:border-blue-300 hover:text-blue-800 transition">

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8.25 7.5V6.108c0-1.135.845-2.098 2.032-2.106.19-.001.379-.002.568-.002 1.778 0 3.5.1 5.15.294a2.25 2.25 0 011.99 2.235v7.971c0 1.124-.83 2.06-1.95 2.108a39.03 39.03 0 01-1.79.038M8.25 7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h6a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H8.25z" />
                                </svg>

                                <span x-text="copied ? 'Copied' : 'Copy request details'"></span>
                            </button>

                            <button type="button"
                                    @click="close()"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl
                                           bg-blue-700 text-white text-xs font-bold hover:bg-blue-800 transition">
                                Close
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <script>
        function libraryServices(cards, drawings) {
            return {
                services: cards || [],
                icons: drawings || {},
                search: '',
                category: 'all',
                active: null,
                copied: false,

                // Search and chips share one predicate, so the count above the
                // grid and the cards below it never disagree.
                get filtered() {
                    const term = this.search.trim().toLowerCase();

                    return this.services.filter((service) => {
                        if (this.category !== 'all' && service.category !== this.category) {
                            return false;
                        }

                        if (term === '') {
                            return true;
                        }

                        return (service.name + ' ' + service.summary + ' ' + service.categoryLabel)
                            .toLowerCase()
                            .includes(term);
                    });
                },

                get activeIcon() {
                    if (! this.active) {
                        return this.icons.book;
                    }

                    return this.icons[this.active.icon] || this.icons.book;
                },

                visible(slug) {
                    return this.filtered.some((service) => service.slug === slug);
                },

                openService(slug) {
                    this.active = this.services.find((service) => service.slug === slug) || null;
                    this.copied = false;
                },

                close() {
                    this.active = null;
                    this.copied = false;
                },

                requestText() {
                    if (! this.active) {
                        return '';
                    }

                    return [
                        'Library service request: ' + this.active.name,
                        '',
                        'What I need: ',
                        '',
                        'Prepared: ' + (this.active.requirements || []).join('; '),
                        'Name: ',
                        'Program and year: ',
                        'Email or Messenger: ',
                    ].join('\n');
                },

                async copyDetails() {
                    const text = this.requestText();

                    if (text === '') {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(text);
                        this.copied = true;
                    } catch (error) {
                        // Clipboard access is refused outside a secure context:
                        // fall back to a temporary selection.
                        const field = document.createElement('textarea');
                        field.value = text;
                        field.setAttribute('readonly', '');
                        field.style.position = 'fixed';
                        field.style.opacity = '0';

                        document.body.appendChild(field);
                        field.select();

                        try {
                            this.copied = document.execCommand('copy');
                        } catch (ignored) {
                            this.copied = false;
                        }

                        document.body.removeChild(field);
                    }

                    if (this.copied) {
                        setTimeout(() => { this.copied = false; }, 2500);
                    }
                },
            };
        }
    </script>

</x-app-layout>

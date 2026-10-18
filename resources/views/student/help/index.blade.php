<x-app-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <h1 class="text-2xl font-extrabold text-gray-900">Help &amp; FAQs</h1>
            <p class="text-sm text-gray-500 mt-1">Guidelines, frequently asked questions, and the campus office directory.</p>

            {{-- Guidelines/FAQs --}}
            <div class="mt-8 space-y-3">
                @foreach ($faqs as $faq)
                    <details class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-5 py-4 cursor-pointer list-none hover:bg-gray-50 transition">
                            <span class="font-semibold text-gray-900 text-sm">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 text-gray-400 shrink-0 transition group-open:rotate-180"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <p class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>

            {{-- Office Directory --}}
            <h2 class="mt-10 text-lg font-bold text-gray-900">Office Directory</h2>
            <p class="text-sm text-gray-500 mt-1">Where to go and when for your concerns.</p>

            <div class="mt-5 grid sm:grid-cols-2 gap-4">
                @foreach ($offices as $office)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-bold text-gray-900 text-sm">{{ $office->value }}</h3>
                            <span class="text-[10px] font-bold uppercase tracking-wide text-blue-700 bg-blue-50 ring-1 ring-blue-100 px-2 py-0.5 rounded-full">
                                {{ $office->value }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-700 mt-2">{{ $office->label() }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $office->details()['description'] }}</p>
                        <div class="mt-3 pt-3 border-t border-gray-100 space-y-1 text-xs text-gray-500">
                            <p class="flex items-start gap-1.5"><svg class="w-4 h-4 mt-0.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg><span>{{ $office->details()['location'] }}</span></p>
                            <p class="flex items-start gap-1.5"><svg class="w-4 h-4 mt-0.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span>{{ $office->details()['hours'] }}</span></p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
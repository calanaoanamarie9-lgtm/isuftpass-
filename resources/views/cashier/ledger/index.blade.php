<x-app-layout>
    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">Student Ledger</h1>
                <p class="text-sm text-gray-500 mt-1">Search student financial records and payment balances.</p>
            </div>

            {{-- Search --}}
            <form method="GET" action="{{ route('cashier.ledger.index') }}"
                  class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">Search</label>
                    <input type="text" name="q" value="{{ $search }}"
                           placeholder="Name, email, course, or contact number"
                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div class="flex gap-2 items-end">
                    <button type="submit"
                            class="flex-1 sm:flex-none px-5 py-2.5 bg-blue-800 text-white text-sm font-semibold rounded-xl hover:bg-blue-900 transition">
                        Search
                    </button>
                    @if ($search)
                        <a href="{{ route('cashier.ledger.index') }}"
                           class="px-4 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-50 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- Results --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($students as $student)
                    <a href="{{ route('cashier.ledger.show', $student) }}"
                       class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-blue-900 overflow-hidden flex items-center justify-center text-yellow-400 font-bold text-sm uppercase shrink-0">
                                @if ($student->studentProfile?->avatar)
                                    <img src="{{ $student->studentProfile->avatar_url }}"
                                         alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    {{ substr($student->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate">{{ $student->name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $student->email }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">
                            {{ $student->studentProfile?->course ?: 'No course set' }}
                            @if ($student->studentProfile?->year_level)
                                &middot; Year {{ $student->studentProfile->year_level }}
                            @endif
                        </p>
                        <p class="mt-2 text-xs text-blue-700 font-semibold">View financial record →</p>
                    </a>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                        <p class="font-semibold text-gray-700">{{ $search ? 'No students match your search' : 'No students found' }}</p>
                        <p class="text-sm text-gray-400 mt-1">{{ $search ? 'Try a different name, course, or contact number.' : 'Registered student accounts will appear here.' }}</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
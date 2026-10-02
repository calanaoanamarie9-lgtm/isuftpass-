<x-app-layout>
    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">Consultation Services</h1>
                <p class="text-sm text-gray-500 mt-1">Configure {{ $office }}-specific appointment types.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-gray-900">Your consultation services</h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $services->count() }} {{ Str::plural('service', $services->count()) }} ·
                            students only see the active ones.
                        </p>
                    </div>

                    <a href="{{ route(strtolower($office) . '.consultations.create') }}"
                       class="inline-flex items-center gap-1.5 w-fit px-3.5 py-2 rounded-xl bg-blue-700 text-white text-xs font-bold hover:bg-blue-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add service
                    </a>
                </div>

                <div class="divide-y divide-gray-50">
                    @forelse ($services as $service)
                        <div class="px-5 py-4 flex flex-wrap items-center gap-x-3 gap-y-2 hover:bg-blue-50/30 transition">
                            <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-700 font-bold text-xs shrink-0">
                                📋
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 text-sm">{{ $service->name }}</p>
                                <p class="text-xs text-gray-400">{{ $service->summary() }}</p>
                            </div>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full {{ $service->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $service->is_active ? 'Active' : 'Hidden' }}</span>
                            <a href="{{ route(strtolower($office) . '.consultations.edit', $service) }}"
                               class="text-xs font-bold text-blue-700 hover:text-blue-900 transition">Edit</a>
                            <form method="POST"
                                  action="{{ route(strtolower($office) . '.consultations.destroy', $service) }}"
                                  data-confirm="Students will no longer see this service."
                                  data-confirm-title="Remove this service?"
                                  data-confirm-ok="Yes, remove it">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 transition">Remove</button>
                            </form>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center text-sm text-gray-400">
                            No consultation services yet.
                            <a href="{{ route(strtolower($office) . '.consultations.create') }}" class="font-bold text-blue-700 hover:underline">Add your first service</a>.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

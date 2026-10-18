<x-app-layout>
    @php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag @endphp
    <div class="py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">{{ session('error') }}</div>
            @endif

            @if ($already)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                    <div class="mx-auto w-14 h-14 bg-green-50 rounded-full flex items-center justify-center">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h1 class="mt-4 font-bold text-gray-900">Feedback already submitted</h1>
                    <p class="text-sm text-gray-500 mt-1">You have already rated this service. Thank you!</p>
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex mt-6 px-5 py-2.5 bg-blue-800 text-white text-sm font-semibold rounded-xl hover:bg-blue-900 transition">
                        Back to Dashboard
                    </a>
                </div>
            @else
                <h1 class="text-2xl font-extrabold text-gray-900">Rate This Service</h1>
                <p class="text-sm text-gray-500 mt-1">Your feedback helps us improve our services.</p>

                <form method="POST" action="{{ route('student.feedback.store') }}"
                      class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
                    @csrf
                    <input type="hidden" name="kind" value="{{ $kind }}">
                    <input type="hidden" name="id" value="{{ $feedbackable->id }}">

                    <div class="bg-blue-50 rounded-xl px-4 py-3">
                        <p class="text-xs uppercase tracking-widest text-blue-400 font-semibold">Service</p>
                        <p class="font-bold text-blue-900">
                            {{ $kind === 'appointment' ? $feedbackable->office . ' — ' . $feedbackable->purpose : $feedbackable->documentsSummary() }}
                        </p>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-1" id="stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" data-value="{{ $i }}"
                                        aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}"
                                        class="star text-gray-300 hover:text-yellow-400 transition">
                                    <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"/></svg>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating" value="" required>
                        @error('rating') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-6">
                        <label for="comment" class="block text-sm font-semibold text-gray-700 mb-1.5">Comments (optional)</label>
                        <textarea id="comment" name="comment" rows="4" placeholder="Tell us about your experience..."
                                  class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-6 py-2.5 bg-blue-800 text-white text-sm font-semibold rounded-xl hover:bg-blue-900 transition">
                            Submit Feedback
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if (! $already)
        <script>
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');

            stars.forEach((star, idx) => {
                star.addEventListener('click', () => {
                    ratingInput.value = star.dataset.value;
                    stars.forEach((s, i) => {
                        s.classList.toggle('text-yellow-400', i <= idx);
                        s.classList.toggle('text-gray-300', i > idx);
                    });
                });
            });
        </script>
    @endif
</x-app-layout>
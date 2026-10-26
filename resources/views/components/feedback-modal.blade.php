@props(['enabled' => true])

@if ($enabled)
    {{--
        Post-submission star rating.

        The request/appointment form is marked with data-feedback-form and
        posts in the background (see the script below). On success it fires
        a `feedback-requested` window event carrying the transaction and the
        page to land on; this modal listens for it, so the same component
        works on every request form without knowing which one submitted.
    --}}
    <div
        x-data="{
            open: false,
            payload: null,
            rating: 0,
            hoverRating: 0,
            comment: '',
            submitting: false,
            error: '',
            redirectTo: '',
            labels: ['Poor', 'Fair', 'Good', 'Very good', 'Excellent'],

            show(detail) {
                this.payload = detail || null;
                this.redirectTo = (detail && detail.redirect) || '{{ route('dashboard') }}';
                this.rating = 0;
                this.hoverRating = 0;
                this.comment = '';
                this.error = '';
                this.submitting = false;
                this.open = true;
                this.$nextTick(() => this.$refs.panel?.focus());
            },

            leave() {
                window.location.href = this.redirectTo || '{{ route('dashboard') }}';
            },

            skip() {
                this.open = false;
                this.leave();
            },

            async submit() {
                if (this.rating < 1) {
                    this.error = 'Please choose a star rating before submitting.';
                    return;
                }

                this.submitting = true;
                this.error = '';

                try {
                    const response = await fetch('{{ route('api.feedback.store') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                        },
                        body: JSON.stringify({
                            transaction_type: this.payload ? this.payload.transactionType : null,
                            transaction_id: this.payload ? this.payload.transactionId : null,
                            rating: this.rating,
                            comments: this.comment,
                        }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        this.error = data.message || 'We could not save your feedback. You can skip for now.';
                        this.submitting = false;
                        return;
                    }

                    this.open = false;
                    this.leave();
                } catch (error) {
                    this.error = 'Something went wrong. Please check your connection and try again.';
                    this.submitting = false;
                }
            },
        }"
        x-on:feedback-requested.window="show($event.detail)"
        x-on:keydown.escape.window="open && skip()"
        x-show="open"
        x-cloak
        x-effect="document.body.style.overflow = open ? 'hidden' : ''"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="feedback-modal-title"
        aria-describedby="feedback-modal-subtitle"
    >
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="skip()"></div>

        <div
            x-ref="panel"
            tabindex="-1"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="relative w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl outline-none sm:p-8"
        >
            <button type="button"
                    data-no-confirm
                    @click="skip()"
                    class="absolute right-4 top-4 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                    aria-label="Close and continue">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-50">
                <svg class="h-6 w-6 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"/>
                </svg>
            </div>

            <h3 id="feedback-modal-title" class="mt-4 text-xl font-bold text-gray-800">
                How was your experience?
            </h3>

            <p id="feedback-modal-subtitle" class="mt-1 text-sm text-gray-500">
                Please rate your experience submitting this request to help us improve ISUFSTPass.
            </p>

            {{-- Star rating --}}
            <div class="my-5 flex justify-center gap-2" role="radiogroup" aria-label="Rating out of five stars">
                <template x-for="star in 5" :key="star">
                    <button type="button"
                            data-no-confirm
                            role="radio"
                            :aria-checked="rating === star ? 'true' : 'false'"
                            :aria-label="star === 1 ? '1 star' : star + ' stars'"
                            @click="rating = star"
                            @mouseenter="hoverRating = star"
                            @mouseleave="hoverRating = 0"
                            @focus="hoverRating = star"
                            @blur="hoverRating = 0"
                            class="rounded-lg p-0.5 text-4xl leading-none transition duration-150 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                        <span aria-hidden="true"
                              :class="(hoverRating || rating) >= star ? 'text-amber-400' : 'text-gray-300'"
                              x-text="(hoverRating || rating) >= star ? '★' : '☆'"></span>
                    </button>
                </template>
            </div>

            <p class="h-4 text-xs font-semibold text-gray-500"
               x-text="(hoverRating || rating) ? labels[(hoverRating || rating) - 1] : 'Tap a star to rate'"></p>

            {{-- Optional comments --}}
            <label for="feedback-comments" class="sr-only">Comments or suggestions</label>
            <textarea id="feedback-comments"
                      x-model="comment"
                      rows="3"
                      maxlength="1000"
                      placeholder="Write your comments or suggestions here..."
                      class="mt-3 w-full rounded-lg border border-gray-200 p-3 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"></textarea>

            <p x-show="error" x-text="error" class="mt-2 text-xs font-medium text-red-600" role="alert"></p>

            {{-- Actions --}}
            <div class="mt-5 flex gap-3">
                <button type="button"
                        data-no-confirm
                        @click="skip()"
                        class="w-1/2 rounded-lg py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400">
                    Skip for Now
                </button>

                <button type="button"
                        data-no-confirm
                        @click="submit()"
                        :disabled="submitting"
                        class="inline-flex w-1/2 items-center justify-center gap-2 rounded-lg bg-blue-600 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:cursor-wait disabled:opacity-70">
                    <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Saving...' : 'Submit Feedback'">Submit Feedback</span>
                </button>
            </div>
        </div>
    </div>

    {{--
        Background submission for the form(s) on this page.

        Success answers with JSON instead of a redirect, so the page stays
        put and the modal can open. Validation failures are handed back to
        the browser's own form submission so Laravel renders its error bag
        exactly as before.
    --}}
    <script>
        (function () {
            document.querySelectorAll('form[data-feedback-form]').forEach(function (form) {
                if (form.dataset.feedbackBound === '1') {
                    return;
                }

                form.dataset.feedbackBound = '1';

                form.addEventListener('submit', async function (event) {
                    if (form.dataset.feedbackBypass === '1') {
                        delete form.dataset.feedbackBypass;
                        return;
                    }

                    if (form.dataset.feedbackBusy === '1') {
                        event.preventDefault();
                        return;
                    }

                    event.preventDefault();
                    form.dataset.feedbackBusy = '1';

                    const button = form.querySelector('button[type="submit"]');
                    const label = button ? button.innerHTML : '';

                    const restore = function () {
                        form.dataset.feedbackBusy = '0';

                        if (button) {
                            button.disabled = false;
                            button.innerHTML = label;
                        }
                    };

                    const handBackToBrowser = function () {
                        restore();
                        form.dataset.feedbackBypass = '1';
                        form.submit();
                    };

                    if (button) {
                        button.disabled = true;
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        // A validation failure belongs on the form itself.
                        if (response.status === 422 || response.status === 419) {
                            handBackToBrowser();
                            return;
                        }

                        // The server redirected: follow it instead of posting twice.
                        if (response.redirected) {
                            restore();
                            window.location.href = response.url;
                            return;
                        }

                        const contentType = response.headers.get('content-type') || '';

                        if (! response.ok || ! contentType.includes('application/json')) {
                            restore();

                            if (response.url) {
                                window.location.href = response.url;
                            } else {
                                handBackToBrowser();
                            }

                            return;
                        }

                        const data = await response.json();

                        if (! data || ! data.feedback) {
                            handBackToBrowser();
                            return;
                        }

                        restore();
                        window.dispatchEvent(new CustomEvent('feedback-requested', { detail: data.feedback }));
                    } catch (error) {
                        handBackToBrowser();
                    }
                });
            });
        })();
    </script>
@endif

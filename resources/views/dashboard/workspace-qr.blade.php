<x-app-layout>
    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">QR Scanner &amp; Check-in</h1>
                <p class="text-sm text-gray-500 mt-1">Scan student appointment passes for instant attendance verification.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center" x-data="qrScanner()">
                <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">ðŸ“·</span>
                </div>
                <h2 class="font-bold text-gray-900 text-lg mb-2">Scan QR Code</h2>
                <p class="text-sm text-gray-500 mb-6">Point your camera at a student's QR pass to verify their appointment.</p>

                <input type="text" x-model="token" @keyup.enter="verify()"
                       placeholder="Enter or scan QR token..."
                       class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-4 text-center font-mono">

                <button @click="verify()" :disabled="!token || loading"
                        class="px-6 py-2.5 bg-blue-700 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition disabled:opacity-50">
                    <span x-text="loading ? 'Verifying...' : 'Verify'"></span>
                </button>

                <div x-show="result" x-transition class="mt-6 p-4 rounded-xl text-sm text-left" :class="result?.valid ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                    <p class="font-bold" x-text="result?.message"></p>
                    <template x-if="result?.student">
                        <div class="mt-2 text-xs space-y-0.5">
                            <p><span class="font-semibold">Student:</span> <span x-text="result.student"></span></p>
                            <p><span class="font-semibold">Purpose:</span> <span x-text="result.purpose"></span></p>
                            <p><span class="font-semibold">Schedule:</span> <span x-text="result.schedule"></span></p>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <script>
    function qrScanner() {
        return {
            token: '',
            loading: false,
            result: null,

            async verify() {
                if (!this.token) return;
                this.loading = true;
                this.result = null;
                try {
                    const res = await fetch(`/verify/appointment/${this.token}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.result = await res.json();
                } catch (e) {
                    this.result = { valid: false, message: 'Failed to verify QR code.' };
                } finally {
                    this.loading = false;
                }
            }
        };
    }
    </script>
</x-app-layout>

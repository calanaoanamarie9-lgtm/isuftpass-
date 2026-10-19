@props([
    'office' => '',
    'endpoint' => '',
    'initialMonth' => now()->format('Y-m'),
    'selected' => now()->toDateString(),
    'minMonth' => now()->format('Y-m'),
    'allowPast' => false,
    'navigate' => false,
    'emit' => false,
    'baseUrl' => '',
    'watchOffice' => '',
])

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"
     x-data="{
        office: {{ \Illuminate\Support\Js::from($office) }},
        endpoint: {{ \Illuminate\Support\Js::from($endpoint) }},
        month: {{ \Illuminate\Support\Js::from($initialMonth) }},
        selected: {{ \Illuminate\Support\Js::from($selected) }},
        minMonth: {{ \Illuminate\Support\Js::from($minMonth) }},
        allowPast: {{ $allowPast ? 'true' : 'false' }},
        navigate: {{ $navigate ? 'true' : 'false' }},
        emit: {{ $emit ? 'true' : 'false' }},
        baseUrl: {{ \Illuminate\Support\Js::from($baseUrl) }},
        days: {},
        loading: true,
        months: ['January','February','March','April','May','June','July','August','September','October','November','December'],
        weekdays: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        init() {
            const officeInput = {{ \Illuminate\Support\Js::from($watchOffice) }};
            if (officeInput) {
                const el = document.getElementById(officeInput);
                if (el) el.addEventListener('change', () => { this.office = el.value; this.fetchMonth(); });
            }
            this.fetchMonth();
        },
        fetchMonth() {
            this.loading = true;
            fetch(this.endpoint + '?office=' + encodeURIComponent(this.office) + '&month=' + this.month)
                .then(r => { if (!r.ok) throw new Error('unavailable'); return r.json(); })
                .then(data => { this.days = data; this.loading = false; })
                .catch(() => { this.days = {}; this.loading = false; });
        },
        shiftMonth(dir) {
            const [y, m] = this.month.split('-').map(Number);
            const next = new Date(y, m - 1 + dir, 1);
            const key = next.getFullYear() + '-' + String(next.getMonth() + 1).padStart(2, '0');
            if (dir < 0 && key < this.minMonth) return;
            this.month = key;
            this.fetchMonth();
        },
        canGoPrev() { return this.month > this.minMonth; },
        handleClick(event) {
            const button = event.target.closest('button[data-date]');
            if (!button) return;
            const date = button.dataset.date;
            const info = this.days[date];
            if (!info || (info.past && !this.allowPast)) return;
            if (this.emit) {
                this.selected = date;
                this.$dispatch('date-selected', { date: date });
            } else if (this.navigate) {
                window.location.href = this.baseUrl + '?date=' + date;
            }
        },
        cellClass(date) {
            const info = this.days[date];
            if (!info) return 'bg-gray-50 text-gray-400 cursor-not-allowed';
            if (info.past && !this.allowPast) return 'bg-gray-100 text-gray-400 cursor-not-allowed';
            if (info.past) return 'bg-gray-100 text-gray-500 cursor-pointer hover:bg-gray-200';
            if (info.available) return 'bg-green-500 text-white cursor-pointer hover:bg-green-600 font-semibold';
            return 'bg-red-500 text-white cursor-pointer hover:bg-red-600 font-semibold';
        },
        todayStr() {
            const t = new Date();
            return t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0');
        },
        gridHtml() {
            const [y, m] = this.month.split('-').map(Number);
            const first = new Date(y, m - 1, 1);
            const offset = (first.getDay() + 6) % 7;
            const total = new Date(y, m, 0).getDate();
            const today = this.todayStr();
            let html = '';
            for (let i = 0; i < offset; i++) html += '<div class=&quot;aspect-square&quot;></div>';
            for (let d = 1; d <= total; d++) {
                const date = this.month + '-' + String(d).padStart(2, '0');
                const ring = date === today ? ' ring-2 ring-blue-700 ring-offset-1' : '';
                const sel = date === this.selected ? ' outline outline-2 outline-offset-1 outline-blue-700' : '';
                html += '<button type=&quot;button&quot; data-date=&quot;' + date + '&quot; class=&quot;aspect-square w-full flex items-center justify-center rounded-lg text-sm transition ' + this.cellClass(date) + ring + sel + '&quot;>' + d + '</button>';
            }
            return html;
        }
     }">

    {{-- Header: month navigation --}}
    <div class="flex items-center justify-between mb-4">
        <button type="button" x-on:click="shiftMonth(-1)" x-bind:disabled="!canGoPrev()"
                class="px-3 py-1.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent">
            &larr;
        </button>
        <p class="font-bold text-gray-900 text-sm" x-text="months[Number(month.split('-')[1]) - 1] + ' ' + month.split('-')[0]"></p>
        <button type="button" x-on:click="shiftMonth(1)"
                class="px-3 py-1.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition">
            &rarr;
        </button>
    </div>

    <p x-show="loading" class="text-xs text-blue-600 font-semibold mb-3">Checking availability…</p>

    {{-- Weekday header --}}
    <div class="grid grid-cols-7 gap-1 mb-1">
        <template x-for="day in weekdays" :key="day">
            <div class="text-center text-[10px] font-bold uppercase tracking-wide text-gray-400 py-1" x-text="day"></div>
        </template>
    </div>

    {{-- Day grid --}}
    <div class="grid grid-cols-7 gap-1" x-html="gridHtml()" x-on:click="handleClick($event)"></div>

    {{-- Legend --}}
    <div class="mt-4 flex flex-wrap items-center gap-4 text-[11px] font-semibold text-gray-500">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-500 inline-block"></span> Available</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-500 inline-block"></span> Fully booked / Blocked</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-100 border border-gray-200 inline-block"></span> Past</span>
    </div>
</div>
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
        selectedDate: {{ \Illuminate\Support\Js::from($selected) }},
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
                if (el) {
                    // The form can reach the page with an office already
                    // chosen - the consultation catalogue deep-links one, and
                    // old() restores one after a validation error. Neither
                    // fires a change event, so the calendar would go on
                    // asking the endpoint for an empty office and never
                    // colour a single day: take the value the field holds.
                    this.office = el.value || this.office;
                    el.addEventListener('change', () => { this.office = el.value; this.fetchMonth(); });
                }
            }
            this.fetchMonth();
        },
        fetchMonth() {
            // With no office there is no availability to show, and the
            // endpoint answers with an error page rather than JSON. Skip the
            // request instead of leaving a grid of unclickable cells.
            if (! this.office) {
                this.days = {};
                this.loading = false;
                return;
            }

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
            // A day with nothing open is closed: the office shut it, or every
            // hour of it is already taken. Either way there is nothing to
            // pick, so the click stops here instead of filling in a date the
            // office would refuse.
            if (this.isUnavailable(date)) return;
            if (this.emit) {
                this.selectedDate = date;
                this.$dispatch('date-selected', { date: date });
            } else if (this.navigate) {
                window.location.href = this.baseUrl + '?date=' + date;
            }
        },
        // One method owns every visual state of a day, so a cell can only
        // ever look like one thing. The order below is the order the form
        // cares about: the date it is holding, the current day, an open day,
        // then the days that answer to nothing.
        cellClass(date) {
            const SELECTED   = 'bg-blue-600 text-white font-bold shadow-md scale-105 transition-all';
            const TODAY      = 'bg-green-500 text-white font-bold ring-2 ring-blue-500 ring-offset-1';
            const OPEN       = 'bg-emerald-500 hover:bg-emerald-600 text-white font-semibold';
            // Closed is the one state a day is in when it is not open: the
            // office shut it, or every hour of it is taken. Red says so, and
            // it takes no click - there is nothing behind it to choose.
            const CLOSED       = 'bg-red-500 text-white cursor-not-allowed font-semibold';
            const CLOSED_TODAY = 'bg-red-500 text-white cursor-not-allowed font-bold ring-2 ring-blue-500 ring-offset-1';
            const PAST       = 'bg-gray-100 text-gray-500 cursor-pointer hover:bg-gray-200';
            const GRAY       = 'bg-gray-100 text-gray-400 cursor-not-allowed';
            const EMPTY      = 'bg-gray-50 text-gray-400 cursor-not-allowed';

            const info = this.days[date];
            const picked = date === this.selectedDate;
            const today = date === this.todayStr();

            // The date the form holds: solid primary blue, lifted out of the
            // grid. It outranks every other state, because it is the day the
            // student chose - even if the month has filled up around it.
            if (picked) {
                const live = info && (! info.past || this.allowPast) && ! this.isUnavailable(date);
                return SELECTED + (live ? ' cursor-pointer' : ' cursor-not-allowed');
            }

            // The calendar has nothing to say about this day yet (no office
            // chosen, or the month request failed), or it has passed.
            if (! info) return EMPTY;
            if (info.past && ! this.allowPast) return GRAY;
            if (info.past) return PAST;

            // Today keeps a blue ring so the current day is never lost among
            // the others, open or closed.
            if (today) return info.available ? TODAY : CLOSED_TODAY;

            return info.available ? OPEN : CLOSED;
        },
        // Nothing is open on this day: the office closed it, or every hour of
        // it is already taken. The calendar answers both the same way,
        // because both leave the student with nothing to pick.
        isUnavailable(date) {
            const info = this.days[date];

            if (! info) return false;
            // A past day the caller allows is still a day to choose.
            if (info.past && this.allowPast) return false;

            return ! info.available;
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
            let html = '';
            for (let i = 0; i < offset; i++) html += '<div class=&quot;aspect-square&quot;></div>';
            for (let d = 1; d <= total; d++) {
                const date = this.month + '-' + String(d).padStart(2, '0');
                // A closed day is not a button to press: it is drawn, and
                // nothing about it answers a click.
                const closed = this.isUnavailable(date) ? ' disabled aria-disabled=&quot;true&quot;' : '';
                html += '<button type=&quot;button&quot; data-no-confirm data-date=&quot;' + date + '&quot;' + closed + ' class=&quot;aspect-square w-full flex items-center justify-center rounded-lg text-sm transition ' + this.cellClass(date) + '&quot;>' + d + '</button>';
            }
            return html;
        }
     }">

    {{-- Header: month navigation --}}
    <div class="flex items-center justify-between mb-4">
        <button type="button" data-no-confirm x-on:click="shiftMonth(-1)" x-bind:disabled="!canGoPrev()"
                class="px-3 py-1.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent">
            &larr;
        </button>
        <p class="font-bold text-gray-900 text-sm" x-text="months[Number(month.split('-')[1]) - 1] + ' ' + month.split('-')[0]"></p>
        <button type="button" data-no-confirm x-on:click="shiftMonth(1)"
                class="px-3 py-1.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition">
            &rarr;
        </button>
    </div>

    <p x-show="loading" class="text-xs text-blue-600 font-semibold mb-3">Checking availability…</p>

    {{-- An office decides which dates are open, so a grid with none chosen
         would be a wall of cells that answer to nothing. --}}
    <p x-show="! loading &amp;&amp; ! office"
       x-cloak
       class="text-xs text-gray-500 font-semibold mb-3">
        Select an office above to see which dates are open.
    </p>

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
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-600 shadow-md inline-block"></span> Selected</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-500 ring-2 ring-blue-500 ring-offset-1 inline-block"></span> Today</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span> Available</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-500 inline-block"></span> Closed</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-100 border border-gray-200 inline-block"></span> Past</span>
    </div>
</div>
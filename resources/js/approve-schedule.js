import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

/**
 * The registrar's answer to a booking: approve it, and set the hour the
 * student should turn up.
 *
 * It lives in a module rather than inside an x-data attribute on purpose. A
 * wall of JavaScript inside an HTML attribute is a quoting minefield - one
 * unescaped quote turns the whole expression into a syntax error, and
 * Alpine then drops the entire component silently, leaving a button that
 * does nothing and nothing on screen to say why. Here Vite compiles the
 * file, so a broken expression fails the build instead of the page.
 *
 * The office's hours and whether the popover opens straight away (a submit
 * that bounced back) arrive as arguments from the view.
 */
Alpine.data('approveSchedule', (config = {}) => ({
    open: config.open === true,
    sending: false,
    officeOpen: config.officeOpen || '08:00',
    officeClose: config.officeClose || '17:00',

    // '2026-10-20' read as 'October 20, 2026'. Built by hand so a date-only
    // string is never taken for UTC and shifted a day back.
    dateLabel(value) {
        const [year, month, day] = value.split('-').map(Number);

        return new Date(year, month - 1, day).toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric',
        });
    },

    // '08:00' read as '08:00 AM'.
    clock(value) {
        const [hour, minute] = value.split(':').map(Number);
        const suffix = hour < 12 ? 'AM' : 'PM';

        return String(hour % 12 || 12).padStart(2, '0')
            + ':' + String(minute).padStart(2, '0') + ' ' + suffix;
    },

    minutes(value) {
        const [hour, minute] = value.split(':').map(Number);

        return hour * 60 + minute;
    },

    // The same rules ApproveScheduleRequest enforces, checked before the
    // round trip so an obvious mistake never leaves the browser.
    // Returns the reason, or null when the window is schedulable.
    scheduleProblem(date, start, end) {
        if (! date || ! start || ! end) {
            return 'Pick a date, a start time and an end time first.';
        }

        if (this.minutes(end) <= this.minutes(start)) {
            return 'The window has to run forward - the end time must be after the start time.';
        }

        if (this.minutes(start) < this.minutes(this.officeOpen)
            || this.minutes(end) > this.minutes(this.officeClose)
        ) {
            return 'The window has to sit inside office hours.';
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (new Date(date + 'T00:00:00') < today) {
            return 'The date cannot be in the past.';
        }

        return null;
    },

    async schedule(event) {
        event.preventDefault();

        if (this.sending) {
            return;
        }

        const form = event.target;
        const date = form.scheduled_date.value;
        const start = form.start_time.value;
        const end = form.end_time.value;

        const problem = this.scheduleProblem(date, start, end);

        if (problem) {
            Swal.fire({
                icon: 'warning',
                title: 'Check the schedule',
                text: problem,
                confirmButtonColor: '#1e40af',
            });

            return;
        }

        const confirmed = await Swal.fire({
            title: 'Approve & Schedule Appointment?',
            html: `
                <div class='rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-left'>
                    <p class='text-sm font-bold text-gray-800'>
                        Date: <span class='text-blue-900'>${this.dateLabel(date)}</span>
                    </p>
                    <p class='mt-1 text-sm font-bold text-gray-800'>
                        Time: <span class='text-blue-900'>${this.clock(start)} - ${this.clock(end)}</span>
                    </p>
                </div>
                <p class='mt-3 text-left text-xs font-semibold text-gray-500'>
                    An email notification will be dispatched to the student.
                </p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve & Send Notification',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#1e40af',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        });

        if (! confirmed.isConfirmed) {
            return;
        }

        this.sending = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            const data = await response.json().catch(() => ({}));

            if (! response.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Could not schedule the appointment',
                    text: data.message || 'Something went wrong. Please try again.',
                    confirmButtonColor: '#1e40af',
                });

                return;
            }

            this.open = false;

            await Swal.fire({
                icon: 'success',
                title: 'Appointment Scheduled!',
                html: 'The schedule has been successfully set to <strong class=\'text-blue-900\'>'
                    + this.dateLabel(date) + '</strong> (' + this.clock(start) + ' – ' + this.clock(end)
                    + '). An email notification has been sent to the student.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#1e40af',
                timer: 4000,
                timerProgressBar: true,
            });

            if (data.redirect) {
                window.location.assign(data.redirect);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Could not reach the server',
                text: 'Check the connection and try again.',
                confirmButtonColor: '#1e40af',
            });
        } finally {
            this.sending = false;
        }
    },
}));

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

/**
 * The card that moves the registrar's booking to another day.
 *
 * The registrar is the only one who moves a booking: the student asks, the
 * office answers with the hour, and the registrar is the one who changes it
 * when the office cannot keep it.
 *
 * The card asks for two things - the new day, picked from the calendar, and
 * why the booking moves. The hour the booking already holds stays with it:
 * a booking that moves day keeps the hour the office gave it, and a
 * booking whose office answers with the hour keeps none and waits for one.
 */
Alpine.data('rescheduleCard', (config = {}) => ({
    open: false,
    date: '',
    startTime: '',
    endTime: '',
    reasonPreset: '',
    reasonOther: '',
    submitting: false,
    error: '',
    root: null,

    // What the card is allowed to ask for.
    showReason: config.showReason !== false,
    requireReason: config.requireReason === true,

    today: new Date().toISOString().split('T')[0],

    init() {
        // $el inside an Alpine expression belongs to the element that
        // expression sits on - not the card. The card's own element is
        // captured here once, and every read of the booking comes from it.
        this.root = this.$el;
    },

    get appointmentId() {
        return this.root ? this.root.dataset.appointmentId || null : null;
    },

    get office() {
        return this.root ? this.root.dataset.office || '' : '';
    },

    get submitUrl() {
        if (!this.root) {
            return '';
        }

        return this.root.dataset.submitUrl || '';
    },

    get currentDate() {
        return this.root ? this.root.dataset.currentDate || '' : '';
    },

    // The hour the booking already had.
    get currentSlot() {
        return this.root ? this.root.dataset.currentSlot || '' : '';
    },

    // The window the booking holds now: the hour it was given, if it holds
    // one, and the hour that hour ends.
    get currentStart() {
        return this.root ? this.root.dataset.currentStart || '' : '';
    },

    get currentEnd() {
        return this.root ? this.root.dataset.currentEnd || '' : '';
    },

    // The hours the office keeps - the time cannot sit outside them.
    get officeOpen() {
        return this.root ? this.root.dataset.officeOpen || '08:00' : '08:00';
    },

    get officeClose() {
        return this.root ? this.root.dataset.officeClose || '17:00' : '17:00';
    },

    // The reason the office gives. A preset is a sentence; "Other" is the
    // registrar's own words.
    get reason() {
        if (this.reasonPreset === 'Other') {
            return this.reasonOther.trim();
        }

        return this.reasonPreset.trim();
    },

    // A day, a window and a reason are what a booking needs to move.
    get canSave() {
        if (!this.date) {
            return false;
        }

        if (!this.startTime) {
            return false;
        }

        if (this.endTime && this.endTime <= this.startTime) {
            return false;
        }

        if (this.requireReason && !this.reason) {
            return false;
        }

        return true;
    },

    // '2026-10-20' as 'October 20, 2026'. Built by hand so a date-only
    // string is never taken for UTC and shifted a day back.
    dateLabel(value) {
        const [year, month, day] = value.split('-').map(Number);

        return new Date(year, month - 1, day).toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric',
        });
    },

    // '09:00' as '9:00' - the clock the fields hold, read the way it is
    // written.
    clock(value) {
        if (!value) {
            return '';
        }

        const [hour, minute] = value.split(':').map(Number);

        return String(hour) + ':' + String(minute).padStart(2, '0');
    },

    // The window, written the way the office says it out loud:
    // "8:40-9:00".
    windowLabel() {
        if (!this.startTime) {
            return '';
        }

        return this.clock(this.startTime)
            + (this.endTime ? '-' + this.clock(this.endTime) : '');
    },

    reset() {
        this.date = '';
        this.startTime = this.currentStart;
        this.endTime = this.currentEnd;
        this.reasonPreset = '';
        this.reasonOther = '';
        this.error = '';
        this.submitting = false;
    },

    // The calendar picked a day; the card holds it.
    pickDate(date) {
        this.date = date;
    },

    // The last look before it is saved: what the booking had, what it will
    // have, and why.
    async confirm() {
        if (!this.canSave) {
            return;
        }

        const confirmed = await Swal.fire({
            title: 'Reschedule Appointment?',
            html: `
                <div class="text-left">
                    <p class="text-[10px] uppercase tracking-widest text-gray-400">Current schedule</p>
                    <p class="text-sm font-bold text-gray-800">${this.currentDate}${this.currentSlot ? ' · ' + this.currentSlot : ''}</p>

                    <p class="text-[10px] uppercase tracking-widest text-gray-400 mt-4">New day</p>
                    <p class="text-sm font-bold text-blue-900">${this.dateLabel(this.date)} · ${this.windowLabel()}</p>

                    <p class="text-[10px] uppercase tracking-widest text-gray-400 mt-4">Reason</p>
                    <p class="text-sm font-semibold text-gray-700">${this.reason}</p>
                </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reschedule',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#1e40af',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        });

        if (!confirmed.isConfirmed) {
            return;
        }

        await this.save();
    },

    async save() {
        if (this.submitting) {
            return;
        }

        this.error = '';
        this.submitting = true;

        try {
            const response = await fetch(this.submitUrl, {
                method: config.method || 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    date: this.date,
                    start_time: this.startTime || null,
                    end_time: this.endTime || null,
                    reschedule_reason: this.reason || null,
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                this.error = data.message
                    || (data.errors ? Object.values(data.errors).flat()[0] : null)
                    || 'Could not reschedule the appointment.';

                return;
            }

            this.open = false;
            this.reset();

            await Swal.fire({
                icon: 'success',
                title: 'Appointment Rescheduled Successfully!',
                text: 'An updated QR pass and email notification have been sent.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#1e40af',
                timer: 4000,
                timerProgressBar: true,
            });

            window.location.assign(data.redirect || config.after || window.location.href);
        } catch (error) {
            this.error = 'Could not reach the server. Check the connection and try again.';
        } finally {
            this.submitting = false;
        }
    },
}));

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

/**
 * The card that moves the registrar's booking to another day and hour.
 *
 * The registrar is the only one who moves a booking: the student asks, the
 * office answers with the hour, and the registrar is the one who changes it
 * when the office cannot keep it.
 *
 * The card takes the booking it works on and hands it the same pieces every
 * time - where the open slots come from and where the new schedule is
 * saved - so the one component serves every page that needs it.
 */
Alpine.data('rescheduleCard', (config = {}) => ({
    open: false,
    date: '',
    time: '',
    reasonPreset: '',
    reasonOther: '',
    slots: [],
    loadingSlots: false,
    submitting: false,
    error: '',
    root: null,

    // What the card is allowed to ask for.
    pickSlot: config.pickSlot !== false,
    showReason: config.showReason !== false,
    requireReason: config.requireReason === true,

    today: new Date().toISOString().split('T')[0],

    init() {
        // $el inside an Alpine expression belongs to the element that
        // expression sits on - the date input, not the card. The card's own
        // element is captured here once, and every read of the booking comes
        // from it.
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

    get currentSlot() {
        return this.root ? this.root.dataset.currentSlot || '' : '';
    },

    // The reason the office gives. A preset is a sentence; "Other" is the
    // registrar's own words.
    get reason() {
        if (this.reasonPreset === 'Other') {
            return this.reasonOther.trim();
        }

        return this.reasonPreset.trim();
    },

    // The date is the one thing every booking needs. A time slot only when
    // the office takes one - the Registrar answers with the hour itself, so
    // the student picks a day and waits.
    get canSave() {
        if (!this.date) {
            return false;
        }

        if (this.pickSlot && !this.time) {
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

    // '08:00' as '08:00 AM'. A slot label already reads as a clock
    // ("09:00 AM - 10:00 AM"), so only a bare H:i is converted.
    clock(value) {
        if (!/^\d{1,2}:\d{2}$/.test(value)) {
            return value;
        }

        const [hour, minute] = value.split(':').map(Number);
        const suffix = hour < 12 ? 'AM' : 'PM';

        return String(hour % 12 || 12).padStart(2, '0')
            + ':' + String(minute).padStart(2, '0') + ' ' + suffix;
    },

    reset() {
        this.date = '';
        this.time = '';
        this.reasonPreset = '';
        this.reasonOther = '';
        this.slots = [];
        this.error = '';
        this.submitting = false;
    },

    async loadSlots() {
        this.slots = [];
        this.time = '';
        this.error = '';

        if (!this.date || !this.pickSlot) {
            return;
        }

        this.loadingSlots = true;

        try {
            const url = new URL(config.slotsUrl, window.location.origin);

            url.searchParams.set('office', this.office);
            url.searchParams.set('date', this.date);

            if (this.appointmentId) {
                url.searchParams.set('ignore_id', this.appointmentId);
            }

            const response = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Could not load the open slots.');
            }

            const list = Array.isArray(data) ? data : (data.slots || []);

            this.slots = list.filter((slot) => slot.is_open && slot.remaining > 0);
        } catch (error) {
            this.error = error.message || 'Could not load the open slots.';
        } finally {
            this.loadingSlots = false;
        }
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
                    <p class="text-sm font-bold text-gray-800">${this.currentDate} · ${this.currentSlot}</p>

                    <p class="text-[10px] uppercase tracking-widest text-gray-400 mt-4">New schedule</p>
                    <p class="text-sm font-bold text-blue-900">${this.dateLabel(this.date)} · ${this.time ? this.clock(this.time) : 'Time set by the office'}</p>

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
                    time_slot: this.time || null,
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
                title: 'Appointment Rescheduled!',
                html: data.message || 'The new schedule has been saved and the student has been notified.',
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

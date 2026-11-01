{{--
    The Appointment Verification Pass at the gate.

    This partial exists only so the guard's scanner can render the one
    shared x-appointment-pass-card — the exact card the student carries,
    the page /verify/appointment/{token} opens, and the office's QR
    verification station shows. The guard never draws its own copy, so the
    reference, office, scheduled time, purpose and current status read at
    the gate can never drift from what the office sees.
--}}

<x-appointment-pass-card :appointment="$appointment" />

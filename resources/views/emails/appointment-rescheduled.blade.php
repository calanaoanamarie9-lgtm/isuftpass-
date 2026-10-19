@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $appointment->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        Your Registrar appointment has been <strong>rescheduled</strong>
        @if ($appointment->reschedule_reason)
            because {{ strtolower($appointment->reschedule_reason) }}.
        @else
            by the office.
        @endif
        Please take note of your new schedule below.
    </p>

    @include('emails.partials.badge', ['slot' => $appointment->status === 'for_reschedule' ? 'For Reschedule' : 'Rescheduled', 'bg' => '#e0e7ff', 'text' => '#3730a3'])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Student Name', 'value' => $appointment->user->name])
        @include('emails.partials.row', ['label' => 'Reference Number', 'value' => $appointment->reference_code])
        @include('emails.partials.row', ['label' => 'Office', 'value' => $appointment->office])
        @include('emails.partials.row', ['label' => 'Previous Schedule', 'value' => $oldSchedule ?? ($appointment->original_date?->format('F j, Y') . ' · ' . ($appointment->original_time_slot ?? '-'))])
        @include('emails.partials.row', ['label' => 'New Date', 'value' => $appointment->date->format('F j, Y (D)')])
        @include('emails.partials.row', ['label' => 'New Time Slot', 'value' => $appointment->time_slot])
        @if ($appointment->reschedule_reason)
            @include('emails.partials.row', ['label' => 'Reason', 'value' => $appointment->reschedule_reason])
        @endif
    </table>

    <p style="margin:22px 0 0;font-size:14px;line-height:1.8;color:#374151;">
        Please log in to your <strong>ISUFSTPASS account</strong> to view your updated appointment details.
    </p>

    @component('emails.partials.notice', [
        'titleText' => 'REMINDER',
        'bg' => '#fefce8',
        'border' => '#facc15',
        'title' => '#a16207',
    ])
        Arrive 10&ndash;15 minutes early and have your Appointment QR Pass ready on your phone for check-in.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.appointments.index'),
        'label' => 'View My Appointments',
    ])

@endsection

@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $appointment->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        Your appointment with the <strong>{{ $appointment->office }}</strong> office scheduled on
        <strong>{{ $appointment->date->format('F j, Y') }}</strong>
        @if ($appointment->time_slot)
            ({{ $appointment->time_slot }})
        @endif
        has been <strong>cancelled</strong>.
    </p>

    @if ($reason)
        <div style="margin-top:4px;margin-bottom:20px;padding:16px 18px;border-radius:10px;background-color:#fef2f2;border-left:4px solid #dc2626;">
            <div style="font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#991b1b;margin-bottom:4px;">
                Cancellation Reason:
            </div>
            <div style="font-size:14px;line-height:1.7;color:#991b1b;">
                {{ $reason }}
            </div>
        </div>
    @endif

    <div style="margin-top:16px;text-align:center;">
        @include('emails.partials.badge', ['slot' => 'Cancelled', 'bg' => '#fee2e2', 'text' => '#991b1b'])
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Student Name', 'value' => $appointment->user->name])
        @include('emails.partials.row', ['label' => 'Reference Number', 'value' => $appointment->reference_code])
        @include('emails.partials.row', ['label' => 'Office / Department', 'value' => $appointment->office])
        @include('emails.partials.row', ['label' => 'Purpose', 'value' => $appointment->purpose])
        @include('emails.partials.row', ['label' => 'Scheduled Date', 'value' => $appointment->date->format('F j, Y (D)')])
        @if ($appointment->time_slot)
            @include('emails.partials.row', ['label' => 'Time Slot', 'value' => $appointment->time_slot])
        @endif
        @if ($reason)
            @include('emails.partials.row', ['label' => 'Reason', 'value' => $reason])
        @endif
    </table>

    @component('emails.partials.notice', [
        'titleText' => 'NEED TO VISIT AGAIN?',
        'bg' => '#f8fafc',
        'border' => '#64748b',
        'title' => '#334155',
    ])
        If you still need assistance from {{ $appointment->office }}, you can book a new appointment at your convenience through the ISUFSTPASS portal.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.appointments.create'),
        'label' => 'Book a New Appointment',
    ])

@endsection

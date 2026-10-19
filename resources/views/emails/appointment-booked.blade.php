@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $appointment->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        This is a reminder for your visit to <strong>{{ $appointment->office }}</strong>
        on <strong>{{ $appointment->date->format('F j, Y') }}</strong>
        @if ($appointment->time_slot)
            during <strong>{{ $appointment->time_slot }}</strong>.
        @else
            &mdash; the office will tell you the exact time once it approves.
        @endif
    </p>

    @include('emails.partials.badge', ['slot' => 'Appointment Reminder', 'bg' => '#dbeafe', 'text' => '#1e40af'])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Office / Department', 'value' => $appointment->office])
        @include('emails.partials.row', ['label' => 'Purpose', 'value' => $appointment->purpose])
        @include('emails.partials.row', ['label' => 'Appointment Date', 'value' => $appointment->date->format('F j, Y (D)')])
        @include('emails.partials.row', ['label' => $appointment->time_slot ? 'Time Slot' : 'Time to Come', 'value' => $appointment->timeToCome()])
        @include('emails.partials.row', ['label' => 'Reference Number', 'value' => $appointment->reference_code])
    </table>

    @component('emails.partials.notice', [
        'titleText' => 'BEFORE YOUR VISIT',
        'bg' => '#fefce8',
        'border' => '#facc15',
        'title' => '#a16207',
    ])
        &bull; Please arrive <strong>10&ndash;15 minutes early</strong>.<br>
        &bull; Have your <strong>Appointment QR Pass ready on your phone</strong> for check-in
        &mdash; find it under "My Digital ID / QR Pass" in the ISUFSTPASS portal.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.pass.show'),
        'label' => 'Open My QR Pass',
    ])

@endsection

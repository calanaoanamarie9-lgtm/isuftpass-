@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $appointment->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        Your appointment request with <strong>{{ $appointment->office }}</strong>
        for <strong>{{ $appointment->date->format('F j, Y') }}</strong>
        has been received.
        @if ($appointment->time_slot)
            You asked for <strong>{{ $appointment->time_slot }}</strong>
            &mdash; the office will confirm the exact time when it approves your request.
        @else
            The office will tell you the exact time once it approves.
        @endif
    </p>

    @include('emails.partials.badge', ['slot' => 'Appointment Request Received', 'bg' => '#dbeafe', 'text' => '#1e40af'])

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
        &bull; Come <strong>15&ndash;30 minutes before</strong> your scheduled time.<br>
        &bull; Failure to arrive on time will result in the
        <strong>automatic cancellation of your request</strong>.<br>
        &bull; Have your <strong>Appointment QR Pass ready on your phone</strong> for check-in
        &mdash; find it under "My Digital ID / QR Pass" in the ISUFSTPASS portal.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.pass.show'),
        'label' => 'Open My QR Pass',
    ])

@endsection

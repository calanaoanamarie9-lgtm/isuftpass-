@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $appointment->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        This is a friendly reminder that your appointment at
        <strong>{{ $appointment->office }}</strong> is
        <strong>tomorrow</strong>.
    </p>

    @include('emails.partials.badge', ['slot' => 'Appointment Tomorrow', 'bg' => '#fef3c7', 'text' => '#92400e'])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Office / Department', 'value' => $appointment->office])
        @include('emails.partials.row', ['label' => 'Purpose', 'value' => $appointment->purpose])
        @include('emails.partials.row', ['label' => 'Appointment Date', 'value' => $appointment->date->format('F j, Y (D)')])
        @include('emails.partials.row', ['label' => 'Come At', 'value' => $appointment->timeToCome()])
        @include('emails.partials.row', ['label' => 'Reference Number', 'value' => $appointment->reference_code])
    </table>

    @component('emails.partials.notice', [
        'titleText' => 'REMEMBER',
        'bg' => '#fefce8',
        'border' => '#facc15',
        'title' => '#a16207',
    ])
        &bull; Come <strong>15&ndash;30 minutes before</strong> your scheduled time.<br>
        &bull; Failure to arrive on time will result in the
        <strong>automatic cancellation of your request</strong>.<br>
        &bull; Bring a <strong>valid school ID</strong>.<br>
        &bull; Have your <strong>QR Pass ready</strong> for check-in &mdash; find it under "My Digital ID / QR Pass" in the ISUFSTPASS portal.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.pass.show'),
        'label' => 'Open My QR Pass',
    ])

@endsection

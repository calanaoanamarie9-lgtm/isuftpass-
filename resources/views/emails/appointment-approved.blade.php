@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $appointment->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        Your appointment at <strong>{{ $appointment->office }}</strong> has been
        <strong>approved</strong>. Please come on
        <strong>{{ $appointment->date->format('F j, Y') }}</strong>
        at <strong>{{ $appointment->confirmed_time }}</strong>.
    </p>

    @include('emails.partials.badge', ['slot' => 'Appointment Approved', 'bg' => '#dcfce7', 'text' => '#166534'])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Office / Department', 'value' => $appointment->office])
        @include('emails.partials.row', ['label' => 'Purpose', 'value' => $appointment->purpose])
        @include('emails.partials.row', ['label' => 'Appointment Date', 'value' => $appointment->date->format('F j, Y (D)')])
        @include('emails.partials.row', ['label' => 'Come At', 'value' => $appointment->confirmed_time])
        @if ($appointment->time_slot && $appointment->time_slot !== $appointment->confirmed_time)
            @include('emails.partials.row', ['label' => 'You Asked For', 'value' => $appointment->time_slot])
        @endif
        @include('emails.partials.row', ['label' => 'Reference Number', 'value' => $appointment->reference_code])
    </table>

    @component('emails.partials.notice', [
        'titleText' => 'BEFORE YOUR VISIT',
        'bg' => '#fefce8',
        'border' => '#facc15',
        'title' => '#a16207',
    ])
        &bull; Please arrive <strong>10&ndash;15 minutes early</strong>.<br>
        &bull; Bring a <strong>valid school ID</strong>.<br>
        &bull; Have your <strong>QR Pass ready</strong> for check-in &mdash; find it under "My Digital ID / QR Pass" in the ISUFSTPASS portal.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.pass.show'),
        'label' => 'Open My QR Pass',
    ])

@endsection

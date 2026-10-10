@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $applicant->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        Your office / staff registration has been
        <strong>approved</strong> by the administrator. You may now sign
        in to ISUFSTPASS with the email and password you registered.
    </p>

    @include('emails.partials.badge', ['slot' => 'Account Approved', 'bg' => '#dcfce7', 'text' => '#166534'])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Office / Department', 'value' => $applicant->office ?: 'Not specified'])
        @include('emails.partials.row', ['label' => 'Position', 'value' => $applicant->position ?: 'Not specified'])
        @include('emails.partials.row', ['label' => 'Account Email', 'value' => $applicant->email])
    </table>

    @include('emails.partials.button', [
        'url' => route('login'),
        'label' => 'Sign In to ISUFSTPASS',
    ])

@endsection

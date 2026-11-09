@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $documentRequest->user->name }},
    </p>

    <p style="margin:0 0 18px;font-size:14px;line-height:1.8;color:#374151;">
        Your document request <strong>{{ $documentRequest->request_number }}</strong>
        for <strong>{{ $documentRequest->documentsSummary() }}</strong> has been
        <strong>approved</strong> and is ready for pick-up.
    </p>

    @include('emails.partials.badge', ['slot' => 'Approved — Ready for Pick-up', 'bg' => '#dcfce7', 'text' => '#166534'])

    <div style="margin-top:26px;padding:20px;border-radius:12px;background-color:#f0fdf4;border:1px solid #bbf7d0;">
        <div style="font-size:11px;font-weight:bold;letter-spacing:1.5px;color:#15803d;margin-bottom:12px;">
            CLAIMING DETAILS
        </div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td style="padding:6px 0;font-size:13px;color:#4b5563;width:42%;">Release Date &amp; Time</td>
                <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:bold;text-align:right;">
                    {{ $documentRequest->ready_at?->format('F j, Y, g:i A') ?? now()->format('F j, Y, g:i A') }}
                </td>
            </tr>
            <tr>
                <td style="padding:6px 0;font-size:13px;color:#4b5563;">Venue</td>
                <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:bold;text-align:right;">Registrar's Office</td>
            </tr>
        </table>
    </div>

    @component('emails.partials.notice', [
        'titleText' => 'REMINDERS',
        'bg' => '#fefce8',
        'border' => '#facc15',
        'title' => '#a16207',
    ])
        &bull; Bring a <strong>valid school or government-issued ID</strong>.<br>
        &bull; Present your <strong>Document Request QR Pass</strong> from the ISUFSTPASS portal (My Digital ID / QR Pass).<br>
        &bull; If sending a representative, provide an <strong>authorization letter</strong> together with a copy of your valid ID.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.documents.show', $documentRequest),
        'label' => 'Open My Claim Slip & QR',
    ])

@endsection

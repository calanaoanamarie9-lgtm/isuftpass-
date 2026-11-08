@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $documentRequest->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        Your request for <strong>{{ $documentRequest->documentsSummary() }}</strong>
        (Request No.: <strong>{{ $documentRequest->request_number }}</strong>) was rejected due to:
    </p>

    <div style="margin-top:4px;padding:16px 18px;border-radius:10px;background-color:#fef2f2;border-left:4px solid #dc2626;">
        <div style="font-size:14px;line-height:1.7;color:#991b1b;font-weight:bold;">
            {{ $reason ?? 'No reason provided by the Registrar\'s Office.' }}
        </div>
    </div>

    <div style="margin-top:14px;text-align:center;">
        @include('emails.partials.badge', ['slot' => 'Action Required', 'bg' => '#fee2e2', 'text' => '#991b1b'])
    </div>

    <div style="margin-top:24px;padding:20px;border-radius:12px;background-color:#f9fafb;border:1px solid #e5e7eb;">
        <div style="font-size:11px;font-weight:bold;letter-spacing:1.5px;color:#374151;margin-bottom:12px;">
            NEXT STEPS
        </div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td style="padding:6px 0;font-size:13px;line-height:1.8;color:#374151;">
                    1. Resolve the issue stated above and resubmit your request through the ISUFSTPASS portal, or<br>
                    2. Visit the Registrar's Office for assistance.
                </td>
            </tr>
        </table>
    </div>

    @include('emails.partials.button', [
        'url' => route('student.documents.create'),
        'label' => 'Submit a New Request',
    ])

@endsection

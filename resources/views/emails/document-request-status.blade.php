@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $documentRequest->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        {{ $updateMessage }}
    </p>

    @include('emails.partials.badge', ['slot' => $statusLabel, 'bg' => '#dbeafe', 'text' => '#1e40af'])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Request Number', 'value' => $documentRequest->request_number])
        @include('emails.partials.row', ['label' => 'Document(s)', 'value' => $documentRequest->documentsSummary()])
        @include('emails.partials.row', ['label' => 'Status', 'value' => $statusLabel])
    </table>

    @component('emails.partials.notice', [
        'titleText' => 'KEEP POSTED',
        'bg' => '#eff6ff',
        'border' => '#071f67',
        'title' => '#071f67',
    ])
        You can track the progress of your request anytime under "My Requests" in your ISUFSTPASS account.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.documents.show', $documentRequest),
        'label' => 'Track My Request',
    ])

@endsection

@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $documentRequest->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        Your document request for <strong>{{ $documentRequest->documentsSummary() }}</strong>
        (Transaction No.: <strong>{{ $documentRequest->request_number }}</strong>)
        with the purpose <strong>{{ \App\Enums\RequestPurposeType::tryFrom($documentRequest->purpose_type)?->label() ?? $documentRequest->purpose_type }}</strong>
        has been received on {{ $documentRequest->submitted_at?->format('F j, Y') ?? now()->format('F j, Y') }}
        and is currently pending review by the Registrar's Office.
    </p>

    @include('emails.partials.badge', ['slot' => 'Pending Review', 'bg' => '#fee2e2', 'text' => '#b91c1c'])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Request Number', 'value' => $documentRequest->request_number])
        @include('emails.partials.row', ['label' => 'Document(s)', 'value' => $documentRequest->documentsSummary()])
        @include('emails.partials.row', ['label' => 'Total Fee', 'value' => '₱'.number_format($totalFee, 2)])
        @include('emails.partials.row', ['label' => 'Date Submitted', 'value' => $documentRequest->submitted_at?->format('F j, Y g:i A')])
    </table>

    @component('emails.partials.notice', [
        'titleText' => 'PAYMENT NOTICE',
        'bg' => '#fef9c3',
        'border' => '#ca8a04',
        'title' => '#854d0e',
    ])
        Your request requires payment of <strong>₱{{ number_format($totalFee, 2) }}</strong>.<br>
        Your request goes first to the <strong>Registrar's Office</strong> for approval. Once it is approved,
        settle your payment at the <strong>University Cashier's Office (over-the-counter)</strong>.<br>
        Your request moves on to release once your payment has been made and verified.
    @endcomponent

    @component('emails.partials.notice', [
        'titleText' => 'KEEP POSTED',
        'bg' => '#eff6ff',
        'border' => '#071f67',
        'title' => '#071f67',
    ])
        You will receive another notification once the status of your request has been updated.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.documents.show', $documentRequest),
        'label' => 'View Claim Slip & QR Code',
    ])

@endsection

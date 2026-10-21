@extends('emails.layouts.app')

@section('content')

    <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
        Hello {{ $appointment->user->name }},
    </p>

    <p style="margin:0 0 22px;font-size:14px;line-height:1.8;color:#374151;">
        Your Registrar appointment has been <strong>rescheduled</strong>
        @if ($appointment->reschedule_reason)
            because {{ strtolower($appointment->reschedule_reason) }}.
        @else
            by the office.
        @endif
        Please take note of your new schedule below.
    </p>

    @include('emails.partials.badge', ['slot' => $appointment->status === 'for_reschedule' ? 'For Reschedule' : 'Rescheduled', 'bg' => '#e0e7ff', 'text' => '#3730a3'])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;">
        @include('emails.partials.row', ['label' => 'Student Name', 'value' => $appointment->user->name])
        @include('emails.partials.row', ['label' => 'Reference Number', 'value' => $appointment->reference_code])
        @include('emails.partials.row', ['label' => 'Office', 'value' => $appointment->office])
        @include('emails.partials.row', ['label' => 'Previous Schedule', 'value' => $oldSchedule ?? ($appointment->original_date?->format('F j, Y') . ' · ' . ($appointment->original_time_slot ?? '-'))])
        @include('emails.partials.row', ['label' => 'New Date', 'value' => $appointment->date->format('F j, Y (D)')])
        @include('emails.partials.row', ['label' => 'New Time Slot', 'value' => $appointment->time_slot])
        @if ($appointment->reschedule_reason)
            @include('emails.partials.row', ['label' => 'Reason', 'value' => $appointment->reschedule_reason])
        @endif
    </table>

    <p style="margin:22px 0 0;font-size:14px;line-height:1.8;color:#374151;">
        Please log in to your <strong>ISUFSTPASS account</strong> to view your updated appointment details.
    </p>


    {{-- The pass itself: what the QR now stands for, and where to open it. --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;border-collapse:collapse;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 10px;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#64748b;">
                    Your updated appointment pass
                </p>

                <p style="margin:0 0 4px;font-size:15px;font-weight:bold;color:#111827;">
                    {{ $appointment->user->name }}
                </p>

                <p style="margin:0 0 12px;font-size:13px;color:#475569;">
                    {{ $appointment->reference_code }} &middot; {{ $appointment->office }} &middot; {{ $appointment->date->format('F j, Y') }}
                </p>

                <p style="margin:0;font-size:12px;line-height:1.7;color:#475569;">
                    Your Appointment QR Pass has been updated for this new schedule.
                    Open it in the portal and have it ready on your phone for check-in.
                </p>

                <p style="margin:12px 0 0;font-size:11px;line-height:1.7;color:#64748b;word-break:break-all;">
                    QR verification link:<br>
                    <a href="{{ $qrUrl }}" style="color:#1e40af;text-decoration:underline;">{{ $qrUrl }}</a>
                </p>
            </td>
        </tr>
    </table>

    @component('emails.partials.notice', [
        'titleText' => 'REMINDER',
        'bg' => '#fefce8',
        'border' => '#facc15',
        'title' => '#a16207',
    ])
        Come <strong>15&ndash;30 minutes before</strong> your scheduled time &mdash; failure to arrive
        on time will result in the <strong>automatic cancellation of your request</strong>.
        Have your Appointment QR Pass ready on your phone for check-in.
    @endcomponent

    @include('emails.partials.button', [
        'url' => route('student.appointments.index'),
        'label' => 'View My Appointments',
    ])

@endsection

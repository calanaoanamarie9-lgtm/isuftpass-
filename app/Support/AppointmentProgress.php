<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;

/**
 * The four steps a booking walks - requested, confirmed, checked in,
 * completed - and the step where an appointment that left that path stopped.
 *
 * A card asks for the steps and draws what comes back. What a status means,
 * which step is the student's position, and where a cancelled booking gave
 * up are decided here once, instead of in every view that draws the path.
 */
class AppointmentProgress
{
    /**
     * The steps in the order a student walks them: key, the label they read
     * (sentence case, the way the rest of the app names things), the column
     * that carries the moment it happened, and the line under the step
     * saying what that stage is - the same way the tracking on My Requests
     * talks about the stages of a document.
     */
    private const STEPS = [
        [
            'key' => 'requested',
            'label' => 'Requested',
            'at' => 'created_at',
            'subtext' => 'Your appointment request has been submitted.',
        ],
        [
            'key' => 'confirmed',
            'label' => 'Confirmed',
            'at' => 'confirmed_at',
            'subtext' => 'The office has approved your schedule and time slot.',
        ],
        [
            'key' => 'checked_in',
            'label' => 'Checked in',
            'at' => 'checked_in_at',
            'subtext' => 'Your QR pass was scanned at the main gate.',
        ],
        [
            'key' => 'completed',
            'label' => 'Completed',
            'at' => 'completed_at',
            'subtext' => 'Your appointment transaction is complete.',
        ],
    ];

    /**
     * Every step with the state a card should draw it in:
     *
     *  - done      the student is past this step
     *  - current   where the appointment stands right now
     *  - ahead     still to come
     *  - stopped   the step an appointment that left the path never reached
     *
     * plus that step's timestamp, when it has one, so a card can show when
     * each thing happened without reading a column itself. A step that has
     * not happened carries no caption rather than an empty one. Its
     * subtext is still there, because that is what the stage says about
     * the booking - only the moment is missing.
     *
     * @return array<int, array{key: string, label: string, state: string, subtext: string, caption: string|null}>
     */
    public static function steps(Appointment $appointment): array
    {
        $status = (string) $appointment->status;
        $position = self::position($status);
        $stoppedAt = $position === null ? self::stoppedAt($appointment, $status) : null;

        $steps = [];

        foreach (self::STEPS as $index => $step) {
            $state = match (true) {
                $stoppedAt !== null && $index === $stoppedAt => 'stopped',
                $stoppedAt !== null && $index > $stoppedAt => 'ahead',
                $stoppedAt !== null => 'done',
                $index < $position => 'done',
                $index === $position && $position === 3 => 'done',
                $index === $position => 'current',
                default => 'ahead',
            };

            $at = $appointment->{$step['at']};

            $steps[] = [
                'key' => $step['key'],
                'label' => $step['label'],
                'state' => $state,
                'subtext' => $step['subtext'],
                'caption' => in_array($state, ['done', 'current'], true) && $at
                    ? $at->format('M j, g:i A')
                    : null,
            ];
        }

        return $steps;
    }

    /**
     * The line that explains an appointment standing still - or null while
     * the path is still open and nothing needs explaining.
     *
     * @return array{tone: string, text: string}|null
     */
    public static function notice(Appointment $appointment): ?array
    {
        return match ((string) $appointment->status) {
            AppointmentStatus::CANCELLED->value => [
                'tone' => 'cancelled',
                'text' => $appointment->cancelled_at
                    ? 'This appointment was cancelled on '
                        .$appointment->cancelled_at->format('F j, Y \a\t g:i A').'.'
                    : 'This appointment was cancelled.',
            ],
            AppointmentStatus::NO_SHOW->value => [
                'tone' => 'no_show',
                'text' => 'The office recorded a no show, because this appointment was never checked in.',
            ],
            AppointmentStatus::FOR_RESCHEDULE->value => [
                'tone' => 'for_reschedule',
                'text' => 'This slot is full. Please wait for the Registrar to move your appointment to the next available schedule.',
            ],
            default => null,
        };
    }

    /**
     * The step a status stands on: steps behind it are done, it is the
     * student's position, steps after it are ahead. Null means the
     * appointment is no longer on the path at all.
     */
    private static function position(string $status): ?int
    {
        return match ($status) {
            AppointmentStatus::PENDING->value,
            AppointmentStatus::FOR_RESCHEDULE->value => 0,
            AppointmentStatus::CONFIRMED->value,
            AppointmentStatus::RESCHEDULED->value => 1,
            AppointmentStatus::CHECKED_IN->value => 2,
            AppointmentStatus::COMPLETED->value => 3,
            default => null,
        };
    }

    /**
     * The step an appointment that left the path stopped in front of: the
     * last one it reached, plus one, capped at the last step.
     *
     * The timestamps answer first, because they are the record of what
     * actually happened - a booking cancelled before the office ever
     * answered stops at Confirmed, one cancelled after the student was
     * already confirmed stops at Checked in.
     */
    private static function stoppedAt(Appointment $appointment, string $status): ?int
    {
        if (! in_array($status, [
            AppointmentStatus::CANCELLED->value,
            AppointmentStatus::NO_SHOW->value,
        ], true)) {
            return null;
        }

        $reached = 0;

        foreach (['completed_at' => 3, 'checked_in_at' => 2, 'confirmed_at' => 1] as $column => $index) {
            if ($appointment->{$column}) {
                $reached = $index;
                break;
            }
        }

        return min($reached + 1, 3);
    }
}

<?php

namespace App\Http\Requests;

use App\Support\TimeSlots;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The office's approval of a booking.
 *
 * One route serves two callers, so two shapes are accepted:
 *  - the registrar page sends a whole window (start_time + end_time) and the
 *    day the student should come (scheduled_date);
 *  - the office dashboards still send the single legacy confirmed_time.
 *
 * Whichever arrives, it must fall inside the office's own hours — the same
 * guard the confirm flow always had, now spelled once, here.
 */
class ApproveScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', $this->route('appointment')) ?? false;
    }

    public function rules(): array
    {
        $hours = TimeSlots::hoursFor($this->route('appointment')->office);

        // A window was sent: validate the full range.
        if ($this->filled('start_time')) {
            return [
                'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
                'start_time' => [
                    'required',
                    'date_format:H:i',
                    'after_or_equal:'.$hours['open'],
                    'before:'.$hours['close'],
                ],
                'end_time' => [
                    'required',
                    'date_format:H:i',
                    'after:start_time',
                    'before_or_equal:'.$hours['close'],
                ],
            ];
        }

        // The office dashboards still answer with one time.
        return [
            'confirmed_time' => [
                'required',
                'date_format:H:i',
                'after_or_equal:'.$hours['open'],
                'before:'.$hours['close'],
            ],
        ];
    }

    public function messages(): array
    {
        $hours = TimeSlots::hoursFor($this->route('appointment')->office);
        $open = self::clock($hours['open']);
        $close = self::clock($hours['close']);

        return [
            'scheduled_date.required' => 'Pick the day the student should come.',
            'scheduled_date.after_or_equal' => 'That day has already passed. Choose today or a later date.',
            'start_time.required' => 'Pick the time you want the student to come.',
            'start_time.date_format' => 'That is not a readable time.',
            'start_time.after_or_equal' => 'The office is not open that early. Choose a time at or after '.$open.'.',
            'start_time.before' => 'The office is closed by then. Choose a time before '.$close.'.',
            'end_time.required' => 'Pick the time the window closes.',
            'end_time.date_format' => 'That is not a readable time.',
            'end_time.after' => 'The end time must come after the start time.',
            'end_time.before_or_equal' => 'The office is closed by '.$close.'. Choose an end time at or before it.',
            'confirmed_time.required' => 'Pick the time you want the student to come.',
            'confirmed_time.date_format' => 'That is not a readable time.',
            'confirmed_time.after_or_equal' => 'The office is not open that early. Choose a time at or after '.$open.'.',
            'confirmed_time.before' => 'The office is closed by then. Choose a time before '.$close.'.',
        ];
    }

    /**
     * An H:i clock time spelled the way the office reads it: 09:00 -> 9:00 AM.
     */
    private static function clock(string $time): string
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return sprintf('%d:%02d %s', $hour % 12 ?: 12, $minute, $hour < 12 ? 'AM' : 'PM');
    }
}

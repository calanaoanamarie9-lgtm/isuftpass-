<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompleteProfileController extends Controller
{
    public const GUEST_PURPOSES = [
        'document' => 'Document Request',
        'appointment' => 'Appointment',
        'transaction' => 'School Transaction',
        'inquiry' => 'Inquiry',
        'visit' => 'Visit',
        'other' => 'Other',
    ];

    public const RELATIONSHIPS = [
        'mother' => 'Mother',
        'father' => 'Father',
        'guardian' => 'Guardian',
        'sibling' => 'Sibling',
        'relative' => 'Relative',
        'other' => 'Other',
    ];

    /**
     * Display the "Complete Personal Details" step.
     */
    public function create(): View
    {
        return view('auth.complete-profile', ['user' => auth()->user()]);
    }

    /**
     * Save the user's personal details, then proceed to the dashboard.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->post();

        // Address, course and year level are required — but only for the
        // student form, the only one that shows them. The alumni, guest and
        // parent forms never submit these fields, so a blanket `required`
        // would bounce every one of those saves. This draws the same line
        // the view draws: it renders the student fields whenever a student
        // profile exists.
        $isStudent = $user->studentProfile !== null;

        // Validate contact_number (required for all)
        $validated = Validator::make($data, [
            'contact_number' => ['required', 'string', 'max:20'],
            'address' => $isStudent ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
            'course' => $isStudent ? ['required', 'string', 'max:100'] : ['nullable', 'string', 'max:100'],
            'year_level' => $isStudent ? ['required', 'string', 'max:20'] : ['nullable', 'string', 'max:20'],
        ])->validate();

        if ($user->studentProfile) {
            $profile = $user->studentProfile ?? $user->studentProfile()->create();

            $profile->update(array_merge(
                array_filter($validated),
                [
                    'student_id' => $request->student_id,
                    'course' => $request->course,
                    'year_level' => $request->year_level,
                    'address' => $request->address,
                ]
            ));
        } else {
            // Determine registration type and apply original validation rules
            $type = $user->registration_type;

            $common = [
                'contact_number' => ['required', 'string', 'max:20'],
            ];

            $rules = match ($type) {
                'alumni' => $common + [
                    // The alumni step asks for what a graduate still has:
                    // a home address and the college they finished. No
                    // student ID (they already left) and no year graduated.
                    'address' => ['required', 'string', 'max:255'],
                    'course' => ['required', 'string', Rule::in(StudentProfile::COLLEGES)],
                ],
                'guest' => $common + [
                    'address' => ['required', 'string', 'max:255'],
                    'purpose' => ['required', 'string', Rule::in(array_keys(self::GUEST_PURPOSES))],
                ],
                'parent' => $common + [
                    'relationship_to_student' => ['required', 'string', Rule::in(array_keys(self::RELATIONSHIPS))],
                    'student_full_name' => ['required', 'string', 'max:255'],
                    'student_id' => ['nullable', 'string', 'max:30'],
                ],
                default => $common,
            };

            $validated = $request->validate($rules);

            $updateData = array_merge($validated, [
                'contact_number' => $request->contact_number,
            ]);

            if ($type === 'alumni') {
                if ($request->has('address')) {
                    $updateData['address'] = $request->address;
                }
                if ($request->has('course')) {
                    $updateData['course'] = $request->course;
                }
            } elseif ($type === 'guest') {
                if ($request->has('address')) {
                    $updateData['address'] = $request->address;
                }
                if ($request->has('purpose')) {
                    $updateData['purpose'] = $request->purpose;
                }
            } elseif ($type === 'parent') {
                if ($request->has('relationship_to_student')) {
                    $updateData['relationship_to_student'] = $request->relationship_to_student;
                }
                if ($request->has('student_full_name')) {
                    $updateData['student_full_name'] = $request->student_full_name;
                }
                if ($request->has('student_id')) {
                    $updateData['student_id'] = $request->student_id;
                }
            }

            $user->update($updateData);
        }

        return redirect()->route('dashboard');
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentProfileController extends Controller
{
    /**
     * Show the student's profile and editable contact details.
     */
    public function show(): View
    {
        $profile = Auth::user()->studentProfile ?? Auth::user()->studentProfile()->create();

        return view('student.profile', ['profile' => $profile]);
    }

    /**
     * Update student profile details (student ID, course, year level, contact info, avatar).
     */
    public function update(Request $request): RedirectResponse
    {
        $profile = Auth::user()->studentProfile ?? Auth::user()->studentProfile()->create();

        $validated = $request->validate([
            'student_id' => ['nullable', 'string', 'max:30', 'unique:student_profiles,student_id,' . $profile->id],
            'course' => ['nullable', 'string', 'max:100'],
            'year_level' => ['nullable', 'string', 'in:' . implode(',', StudentProfile::YEAR_LEVELS)],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        // Removing clears whichever photo the profile card displays: the
        // student-profile upload, the registration upload on the user record,
        // or both — otherwise the card keeps showing the surviving one.
        if ($request->boolean('remove_avatar')) {
            if ($profile->avatar) {
                \Illuminate\Support\Facades\Storage::disk(config('filesystems.avatar'))->delete($profile->avatar);
                $profile->update(['avatar' => null]);
            }

            if (Auth::user()->avatar) {
                \Illuminate\Support\Facades\Storage::disk(config('filesystems.avatar'))->delete(Auth::user()->avatar);
                Auth::user()->update(['avatar' => null]);
            }
        } elseif ($request->hasFile('avatar')) {
            if ($profile->avatar) {
                \Illuminate\Support\Facades\Storage::disk(config('filesystems.avatar'))->delete($profile->avatar);
            }

            $file = $request->file('avatar');
            $name = 'avatars/' . Str::random(40) . '.' . $file->getClientOriginalExtension();
            \Illuminate\Support\Facades\Storage::disk(config('filesystems.avatar'))->put($name, file_get_contents($file->getRealPath()));
            $validated['avatar'] = $name;
        } else {
            unset($validated['avatar']);
        }

        $profile->update($validated);

        return back()->with('status', 'Profile updated successfully.');
    }
}
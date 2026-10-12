<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Models\User;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The one Profile & Account Settings page every role shares. Students used
 * to own the full experience (photo + account info + password + delete)
 * through StudentProfileController while staff only got the Breeze
 * name/email form and offices, departments and alumni got read-only pages -
 * alumni's "update" even persisted nothing. All of those routes now land
 * here so every account manages itself the same way.
 */
class ProfileController extends Controller
{
    /**
     * Display the user's Profile & Account Settings page.
     */
    public function edit(Request $request): View
    {
        return view('profile.settings', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's photo and profile information.
     *
     * Serves two forms that share this endpoint: the photo card (avatar /
     * remove_avatar only) and the account-information card (name + email
     * only), plus the student-profile fields the old student route accepted.
     * Each group therefore validates only what it actually posts.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // An unselected <input type="file"> still posts an empty part
        // (UPLOAD_ERR_NO_FILE), and Laravel's file rules fail that instantly
        // with "failed to upload" - before `nullable` can skip it - which
        // bounced every save made without re-picking a photo. Only a real
        // upload is handed to the validator; otherwise the field is absent
        // and the photo stays as it is.
        $data = $request->post();
        $data['avatar'] = $request->hasFile('avatar') ? $request->file('avatar') : null;

        $rules = [
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            // Student-profile extras (posted by the old /student/profile
            // form); ignored entirely for accounts without a profile row.
            'student_id' => ['sometimes', 'nullable', 'string', 'max:30'],
            'course' => ['sometimes', 'nullable', 'string', 'max:100'],
            'year_level' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', StudentProfile::YEAR_LEVELS)],
            'contact_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];

        if ($user->studentProfile) {
            $rules['student_id'] = [
                'sometimes', 'nullable', 'string', 'max:30',
                Rule::unique('student_profiles', 'student_id')->ignore($user->studentProfile->id),
            ];
        }

        $validated = Validator::make($data, $rules)->validate();

        $this->storeAvatar($request, $user, $validated);

        $profileData = array_intersect_key($validated, array_flip([
            'student_id', 'course', 'year_level', 'contact_number', 'address',
        ]));

        if ($profileData !== [] && $user->studentProfile) {
            $user->studentProfile->update($profileData);
        }

        $accountData = array_intersect_key($validated, array_flip(['name', 'email']));

        if ($accountData !== []) {
            $user->fill($accountData);

            // Measured after fill(): before it nothing is dirty, so the check
            // would never fire and a changed address would keep its old stamp.
            $changedEmail = $user->isDirty('email');

            if ($changedEmail) {
                // A brand new address has never been clicked, so it starts over.
                // Marking it verified here would make the verification flow
                // decorative: the one moment it matters would skip it.
                $user->email_verified_at = null;
            }

            $user->save();

            if ($changedEmail) {
                SafeMailer::verifyEmail($user);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Apply the photo side of the request: removal clears whichever photo
     * the account has on file, an upload replaces it. Student-role accounts
     * keep the photo on the student profile (what their dashboards, QR pass
     * and sidebar read); staff keep it on the user record.
     */
    private function storeAvatar(Request $request, User $user, array $validated): void
    {
        $disk = config('filesystems.avatar');
        $target = $user->studentProfile ?? $user;

        if ($request->boolean('remove_avatar')) {
            if ($target->avatar) {
                Storage::disk($disk)->delete($target->avatar);
                $target->update(['avatar' => null]);
            }

            // A registration-time photo can survive a student-profile removal
            // and would keep rendering through the fallback chain, so clear it
            // too whenever the account has one.
            if ($target instanceof StudentProfile && $user->avatar) {
                Storage::disk($disk)->delete($user->avatar);
                $user->update(['avatar' => null]);
            }

            return;
        }

        if (! $request->hasFile('avatar')) {
            return;
        }

        if ($target->avatar) {
            Storage::disk($disk)->delete($target->avatar);
        }

        $file = $request->file('avatar');
        $name = 'avatars/'.Str::random(40).'.'.$file->getClientOriginalExtension();

        Storage::disk($disk)->put($name, file_get_contents($file->getRealPath()));

        $target->update(['avatar' => $name]);
    }
}

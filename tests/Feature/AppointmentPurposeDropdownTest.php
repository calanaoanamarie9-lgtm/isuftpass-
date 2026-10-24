<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AppointmentPurposes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The booking form's Purpose field is a dynamic dropdown: each office offers
 * its own list (the Registrar offers its standard documents / services) plus an
 * "Other" option that reveals a mandatory "Please specify purpose" box.
 */
class AppointmentPurposeDropdownTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    public function test_the_booking_form_offers_the_registrar_purpose_list_and_other(): void
    {
        $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create', ['office' => 'Registrar']))
            ->assertOk()
            ->assertSee('Transcript of Records')
            ->assertSee('Good Moral Certificate')
            ->assertSee(AppointmentPurposes::OTHER);
    }

    public function test_a_preset_purpose_is_saved_exactly_as_chosen(): void
    {
        Mail::fake();
        Notification::fake();

        $student = $this->makeStudent();

        $this->actingAs($student)
            ->post(route('student.appointments.store'), [
                'office' => 'Registrar',
                'purpose' => 'Transcript of Records',
                'date' => now()->addDays(3)->toDateString(),
            ])
            ->assertRedirect(route('student.appointments.index'));

        $this->assertSame(
            'Transcript of Records',
            $student->appointments()->firstOrFail()->purpose
        );
    }

    /**
     * The form's hidden "Please specify purpose" box submits anyway, emptied -
     * the TrimStrings / ConvertEmptyStringsToNull middleware turns that into
     * null, which the strict `string` rule used to reject with "The purpose
     * other field must be a string." A preset purpose must book cleanly with
     * the box submitted empty or missing entirely.
     */
    public function test_a_preset_purpose_books_cleanly_when_the_specify_box_submits_empty(): void
    {
        Mail::fake();
        Notification::fake();

        foreach (['', null] as $i => $emptyPurposeOther) {
            $student = $this->makeStudent();

            $this->actingAs($student)
                ->post(route('student.appointments.store'), [
                    'office' => 'Registrar',
                    'purpose' => 'Good Moral Certificate',
                    'purpose_other' => $emptyPurposeOther,
                    'date' => now()->addDays(3 + $i)->toDateString(),
                ])
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('student.appointments.index'));

            $this->assertSame(
                'Good Moral Certificate',
                $student->appointments()->firstOrFail()->purpose
            );
        }
    }

    public function test_choosing_other_stores_the_typed_purpose(): void
    {
        Mail::fake();
        Notification::fake();

        $student = $this->makeStudent();

        $this->actingAs($student)
            ->post(route('student.appointments.store'), [
                'office' => 'Registrar',
                'purpose' => AppointmentPurposes::OTHER,
                'purpose_other' => 'Request a correction on my surname',
                'date' => now()->addDays(3)->toDateString(),
            ])
            ->assertRedirect(route('student.appointments.index'));

        $this->assertSame(
            'Request a correction on my surname',
            $student->appointments()->firstOrFail()->purpose
        );
    }

    public function test_choosing_other_requires_a_specified_purpose(): void
    {
        Mail::fake();
        Notification::fake();

        // Both an emptied box and a missing/null one must bounce back -
        // required_if alone no longer catches nulls on a nullable field.
        foreach (['', null, '   '] as $i => $emptyPurposeOther) {
            $student = $this->makeStudent();

            $this->actingAs($student)
                ->post(route('student.appointments.store'), [
                    'office' => 'Registrar',
                    'purpose' => AppointmentPurposes::OTHER,
                    'purpose_other' => $emptyPurposeOther,
                    'date' => now()->addDays(3 + $i)->toDateString(),
                ])
                ->assertSessionHasErrors('purpose_other');

            $this->assertSame(0, $student->appointments()->count());
        }
    }

    /**
     * Picking "Other" only reveals the specify box if the Purpose select
     * itself listens for change - wiring it only inside the office-change
     * rebuild meant the box never appeared for a student, even though every
     * server-side case passed. Guard the markup keeps that listener, and
     * that a preset choice empties and disables the box again.
     */
    public function test_the_purpose_select_reveals_the_specify_box_on_change(): void
    {
        $html = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            "/purposeSelect\\.addEventListener\\(\\s*'change',\\s*toggleOtherPurpose\\s*\\)/",
            $html,
            'The Purpose dropdown must call toggleOtherPurpose() when the student picks an option.'
        );

        $this->assertStringContainsString(
            'purposeOtherInput.disabled = ! isOther',
            $html,
            'A preset purpose must disable the hidden specify box so it is not submitted.'
        );

        $this->assertStringContainsString(
            "purposeOtherInput.value = ''",
            $html,
            'A preset purpose must clear any text typed into the specify box.'
        );
    }

    /**
     * Server-side the box starts disabled whenever "Other" is not the chosen
     * purpose, so even without JavaScript a preset booking never submits it.
     */
    public function test_the_specify_box_renders_disabled_for_a_preset_purpose(): void
    {
        $html = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create', [
                'office' => 'Accounting',
                'purpose' => 'Payment Concerns',
            ]))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<input id="purpose_other"[^>]*\bdisabled\b/',
            $html,
            'The specify box must render disabled when a preset purpose is already chosen.'
        );
    }
}

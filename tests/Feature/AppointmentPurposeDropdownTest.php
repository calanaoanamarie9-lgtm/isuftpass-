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

        $student = $this->makeStudent();

        $this->actingAs($student)
            ->post(route('student.appointments.store'), [
                'office' => 'Registrar',
                'purpose' => AppointmentPurposes::OTHER,
                'purpose_other' => '',
                'date' => now()->addDays(3)->toDateString(),
            ])
            ->assertSessionHasErrors('purpose_other');

        $this->assertSame(0, $student->appointments()->count());
    }
}

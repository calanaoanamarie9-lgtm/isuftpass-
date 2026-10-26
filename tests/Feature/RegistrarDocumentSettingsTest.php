<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrarDocumentSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function makeStudentWithRequest(): array
    {
        $student = User::factory()->create(['role' => 'student']);
        $document = Document::create(['name' => 'Transcript of Records', 'description' => 'TOR', 'fee' => 100.00]);

        $request = $student->documentRequests()->create([
            'student_name' => $student->name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => DocumentRequestStatus::SUBMITTED->value,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
        ]);
        $request->documents()->attach($document->id);

        return [$student, $request, $document];
    }

    public function test_registrar_can_view_document_settings(): void
    {
        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/documents')
            ->assertOk()
            ->assertSee('Document Fees & Services', false)
            ->assertSee('Manage document types, service fees, and availability.');
    }

    public function test_registrar_can_add_and_update_document_fee(): void
    {
        $registrar = $this->makeRegistrar();

        $this->actingAs($registrar)
            ->post('/registrar/documents', [
                'name' => 'Certificate of Enrollment',
                'description' => 'COE',
                'fee' => 50.00,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $document = Document::where('name', 'Certificate of Enrollment')->first();
        $this->assertNotNull($document);

        $this->actingAs($registrar)
            ->put("/registrar/documents/{$document->id}", [
                'name' => 'Certificate of Enrollment',
                'fee' => 75.00,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertEquals('75.00', $document->fresh()->fee);
    }

    public function test_document_used_by_requests_is_removed_from_the_catalogue_instead_of_deleted(): void
    {
        [, , $document] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->delete("/registrar/documents/{$document->id}")
            ->assertRedirect()
            ->assertSessionHas('status');

        // The record stays so past requests still name what was asked for,
        // but students no longer see it in the request form.
        $this->assertNotNull($document->fresh());
        $this->assertFalse($document->fresh()->is_active);
    }

    public function test_unused_document_is_deleted_outright(): void
    {
        $document = Document::create([
            'name' => 'Certificate of Good Standing',
            'description' => 'CGS',
            'fee' => 40.00,
        ]);

        $this->actingAs($this->makeRegistrar())
            ->delete("/registrar/documents/{$document->id}")
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertNull($document->fresh());
    }

    public function test_the_services_list_offers_a_remove_button_even_when_the_service_is_in_use(): void
    {
        [, , $document] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/documents')
            ->assertOk()
            ->assertSee('Remove')
            ->assertSee('Edit')
            ->assertSee('form="remove-document-'.$document->id.'"', false)
            // The usage chip was removed; the row now shows Edit and Remove.
            ->assertDontSee('Used by');
    }

    public function test_cashier_cannot_access_registrar_document_settings(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get('/registrar/documents')
            ->assertForbidden();
    }

    public function test_student_cannot_access_registrar_document_settings(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post('/registrar/documents', [
                'name' => 'TOR',
                'description' => 'TOR',
                'fee' => 100.00,
            ])
            ->assertForbidden();
    }
}
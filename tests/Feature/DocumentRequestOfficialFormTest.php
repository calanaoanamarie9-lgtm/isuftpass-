<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentRequestOfficialFormTest extends TestCase
{
    use RefreshDatabase;

    private function makeDocuments(): array
    {
        return [
            Document::create(['name' => 'Transcript of Record (TOR)', 'description' => 'TOR', 'fee' => 100.00]),
            Document::create(['name' => 'Diploma', 'description' => 'Diploma', 'fee' => 250.00]),
            Document::create(['name' => 'Good Moral Character', 'description' => 'GMC', 'fee' => 50.00]),
        ];
    }

    private function createStudent(): User
    {
        $user = User::factory()->create(['role' => 'student', 'name' => 'Juan Dela Cruz']);
        $user->studentProfile()->create([
            'course' => 'BSIT',
            'year_level' => '3rd Year',
            'contact_number' => '09171234567',
            'address' => 'Barangay Uno, Iloilo City',
        ]);

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'document_ids' => [1],
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
        ], $overrides);
    }

    public function test_create_page_renders_official_form_sections(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->get('/student/document-requests/create')
            ->assertOk()
            ->assertSee('Document Selection')
            ->assertSee('Purpose of Request')
            ->assertSee('Student Information')
            ->assertSee('Mode of Claiming')
            ->assertSee($documents[0]->name)
            ->assertSee('I shall come back for my record personally.');
    }

    public function test_request_can_be_submitted_with_multiple_documents_and_representative(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $response = $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id, $documents[1]->id],
                'purpose_type' => 'transfer',
                'transfer_to' => 'UPLB, Los Banos, Laguna',
                'educational_status' => 'graduated',
                'educational_level' => 'college',
                'claim_mode' => 'representative',
                'representative_name' => 'Maria Santos',
            ]));

        $request = $user->documentRequests()->first();

        $response->assertRedirect(route('student.documents.index'));

        $this->assertNotNull($request);
        $this->assertCount(2, $request->documents);
        $this->assertEquals('UPLB, Los Banos, Laguna', $request->transfer_to);
        $this->assertEquals('Maria Santos', $request->representative_name);
        $this->assertEquals('Juan Dela Cruz', $request->student_name);
        $this->assertEquals('BSIT 3rd Year', $request->student_course_year);
        $this->assertEquals('Barangay Uno, Iloilo City', $request->student_address);
        $this->assertEquals('09171234567', $request->student_contact);
        $this->assertEquals(1, $user->notifications()->count());
    }

    public function test_submission_shows_instructional_banner(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]))
            ->assertRedirect(route('student.documents.index'))
            ->assertSessionHas('instructions');

        $this->actingAs($user)
            ->get(route('student.documents.index'))
            ->assertOk()
            ->assertSee('What should I do next?')
            ->assertSee('Proceed to the Cashier to settle your payment');
    }

    public function test_submission_popup_asks_the_student_to_print_the_form(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]))
            ->assertRedirect(route('student.documents.index'));

        // The success popup on the page the submit lands on.
        $this->actingAs($user)
            ->get(route('student.documents.index'))
            ->assertOk()
            ->assertSee('Please print the requisition form before going to the registrar to submit it');
    }

    public function test_others_requires_specification(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
                'others' => '1',
                'others_specification' => '',
            ]))
            ->assertSessionHasErrors('others_specification');
    }

    public function test_conditional_fields_accept_missing_or_array_values(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
                'others_specification' => ['Some document'],
                'transfer_to' => [],
                'representative_name' => ['Rep Name'],
            ]))
            ->assertRedirect();

        $request = $user->documentRequests()->first();
        $this->assertNotNull($request);
        $this->assertNull($request->others_specification);
        $this->assertNull($request->transfer_to);
        $this->assertEquals('Rep Name', $request->representative_name);
    }

    public function test_others_specification_is_stored(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
                'others' => '1',
                'others_specification' => 'Certificate of Enrollment',
            ]))
            ->assertRedirect();

        $this->assertEquals('Certificate of Enrollment', $user->documentRequests()->first()->others_specification);
    }

    public function test_representative_mode_requires_name(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
                'claim_mode' => 'representative',
                'representative_name' => '',
            ]))
            ->assertSessionHasErrors('representative_name');
    }

    public function test_transfer_requires_school_info(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
                'purpose_type' => 'transfer',
            ]))
            ->assertSessionHasErrors('transfer_to');
    }

    public function test_show_page_displays_request_details(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id, $documents[2]->id],
                'claim_mode' => 'representative',
                'representative_name' => 'Maria Santos',
            ]));

        $request = $user->documentRequests()->first();

        $this->actingAs($user)
            ->get('/student/document-requests/' . $request->id)
            ->assertOk()
            ->assertSee($request->request_number)
            // The sentence wraps across two source lines in the view, so
            // assert on the fragment that stays on one line.
            ->assertSee('Present this QR code to authorized staff');

        $this->assertNotNull($request->claim_token);
        $this->assertNotEquals($request->claim_token, $request->request_number);
    }

    public function test_show_page_renders_scannable_claim_qr(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]))
            ->assertRedirect();

        $request = $user->documentRequests()->first();

        $this->actingAs($user)
            ->get('/student/document-requests/' . $request->id)
            ->assertOk()
            ->assertSee('Present this QR code to authorized staff')
            ->assertSee('data:image/svg+xml')
            ->assertSee('Download QR');
    }

    public function test_student_can_download_full_document_qr_card(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]))
            ->assertRedirect();

        $request = $user->documentRequests()->first();

        $response = $this->actingAs($user)
            ->get(route('student.documents.qr.download', ['documentRequest' => $request, 'format' => 'svg']));

        $response->assertOk();

        $svg = $response->getContent();

        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.svg"', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ISUFSTPASS', $svg);
        $this->assertStringContainsString('DOCUMENT REQUEST QR', $svg);
        $this->assertStringContainsString($request->request_number, $svg);
        $this->assertStringContainsString($user->name, $svg);
        $this->assertStringContainsString('ISUFSTPASS VERIFIED', $svg);
    }

    /**
     * The bare download — the URL the "Download QR Code" button points at —
     * must be a PNG, since a phone cannot open the SVG this used to serve
     * and reports "Couldn't open file".
     */
    public function test_document_qr_download_defaults_to_png_a_phone_can_open(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]))
            ->assertRedirect();

        $request = $user->documentRequests()->first();

        $response = $this->actingAs($user)
            ->get(route('student.documents.qr.download', $request));

        $response->assertOk();

        $png = $response->getContent();

        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.png"', $response->headers->get('Content-Disposition'));
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
        $this->assertSame((string) strlen($png), $response->headers->get('Content-Length'));

        $image = @imagecreatefromstring($png);
        $this->assertNotFalse($image, 'The download must decode as an image.');
        $this->assertSame(640, imagesx($image));
        $this->assertSame(1010, imagesy($image));
    }

    public function test_student_can_open_printable_requisition_form(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]))
            ->assertRedirect();

        $request = $user->documentRequests()->first();

        $this->actingAs($user)
            ->get(route('student.documents.requisition', $request))
            ->assertOk()
            ->assertSee('REQUISITION FORM')
            ->assertSee($request->request_number)
            ->assertSee('Juan')
            ->assertSee('Dela')
            ->assertSee('Cruz')
            ->assertSee(route('student.documents.show', $request))
            ->assertDontSee(route('registrar.document-requests.show', $request));
    }

    public function test_other_student_cannot_open_requisition_form_of_another_request(): void
    {
        $documents = $this->makeDocuments();
        $owner = $this->createStudent();
        $other = $this->createStudent();

        $this->actingAs($owner)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]))
            ->assertRedirect();

        $request = $owner->documentRequests()->first();

        $this->actingAs($other)
            ->get(route('student.documents.requisition', $request))
            ->assertForbidden();
    }

    public function test_index_lists_document_names(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]));

        $this->actingAs($user)
            ->get('/student/document-requests')
            ->assertOk()
            ->assertSee('Transcript of Record (TOR)');
    }

    public function test_edit_page_renders_prefilled_form(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id, $documents[1]->id],
                'purpose_type' => 'transfer',
                'transfer_to' => 'UPLB, Los Banos, Laguna',
            ]));

        $request = $user->documentRequests()->first();

        $this->actingAs($user)
            ->get('/student/document-requests/' . $request->id . '/edit')
            ->assertOk()
            ->assertSee('Edit Request')
            ->assertSee('UPLB, Los Banos, Laguna')
            ->assertSee('Save Changes');
    }

    public function test_student_can_update_own_submitted_request(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
                'purpose_type' => 'employment',
            ]));

        $request = $user->documentRequests()->first();

        $this->actingAs($user)
            ->put('/student/document-requests/' . $request->id, [
                'document_ids' => [$documents[2]->id],
                'purpose_type' => 'transfer',
                'transfer_to' => 'WVSU, Iloilo City',
                'educational_status' => 'graduated',
                'educational_level' => 'college',
                'claim_mode' => 'personal',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $request->refresh();
        $this->assertEquals('transfer', $request->purpose_type);
        $this->assertEquals('WVSU, Iloilo City', $request->transfer_to);
        $this->assertTrue($request->documents()->pluck('documents.id')->contains($documents[2]->id));
        $this->assertFalse($request->documents()->pluck('documents.id')->contains($documents[0]->id));
        $this->assertGreaterThanOrEqual(1, \App\Models\AuditLog::where('action', 'request.updated')->count());
    }

    public function test_student_cannot_edit_processed_request(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]));

        $request = $user->documentRequests()->first();
        $request->update(['status' => 'processing', 'processing_at' => now()]);

        $this->actingAs($user)
            ->get('/student/document-requests/' . $request->id . '/edit')
            ->assertForbidden();

        $this->actingAs($user)
            ->put('/student/document-requests/' . $request->id, $this->validPayload())
            ->assertForbidden();
    }

    public function test_student_can_cancel_own_active_request(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]));

        $request = $user->documentRequests()->first();

        $this->actingAs($user)
            ->post('/student/document-requests/' . $request->id . '/cancel')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals('cancelled', $request->status);
        $this->assertNotNull($request->cancelled_at);
        $this->assertCount(1, $user->notifications()->where('type', \App\Notifications\DocumentRequestStatusNotification::class)->get());
    }

    public function test_student_can_delete_cancelled_request(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]));

        $request = $user->documentRequests()->first();
        $request->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $this->actingAs($user)
            ->delete('/student/document-requests/' . $request->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('document_requests', ['id' => $request->id]);
    }

    public function test_student_cannot_delete_active_request(): void
    {
        $documents = $this->makeDocuments();
        $user = $this->createStudent();

        $this->actingAs($user)
            ->post('/student/document-requests', $this->validPayload([
                'document_ids' => [$documents[0]->id],
            ]));

        $request = $user->documentRequests()->first();

        $this->actingAs($user)
            ->delete('/student/document-requests/' . $request->id)
            ->assertForbidden();

        $this->assertDatabaseHas('document_requests', ['id' => $request->id]);
    }
}
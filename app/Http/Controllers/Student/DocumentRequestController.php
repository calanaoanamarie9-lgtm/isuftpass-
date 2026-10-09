<?php

namespace App\Http\Controllers\Student;

use App\Enums\ClaimMode;
use App\Enums\DocumentRequestStatus;
use App\Enums\EducationalLevel;
use App\Enums\EducationalStatus;
use App\Enums\Office;
use App\Enums\RequestPurposeType;
use App\Http\Controllers\Controller;
use App\Mail\DocumentRequestReceived;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Notifications\DocumentRequestReceivedNotification;
use App\Notifications\DocumentRequestStatusNotification;
use App\Support\AuditLogger;
use App\Support\RegistrarNotifier;
use App\Support\SafeMailer;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    /**
     * Request history: active requests and completed archives.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        $requests = Auth::user()->documentRequests()
            ->with('documents')
            ->when($tab === 'active', fn ($q) => $q->active()->latest())
            ->when($tab === 'archived', fn ($q) => $q->archived()->latest())
            ->paginate(10)
            ->withQueryString();

        return view('student.documents.index', [
            'requests' => $requests,
            'tab' => $tab,
        ]);
    }

    public function create(): View
    {
        return view('student.documents.create', [
            'documents' => Document::active()->orderBy('name')->get(),
            'offices' => Office::toSelect(),
            'purposeTypes' => RequestPurposeType::toSelect(),
            'educationalStatuses' => EducationalStatus::toSelect(),
            'educationalLevels' => EducationalLevel::toSelect(),
            'claimModes' => ClaimMode::toSelectKeys(),
            'profile' => Auth::user()->studentProfile,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);

        $profile = Auth::user()->studentProfile;

        $documentRequest = Auth::user()->documentRequests()->create([
            'student_name' => Auth::user()->name,
            'student_address' => $profile?->address ?? 'Not provided',
            'student_contact' => $profile?->contact_number ?? 'Not provided',
            'student_course_year' => trim(($profile?->course ?? '') . ' ' . ($profile?->year_level ?? '')) ?: 'Not provided',
            'purpose_type' => $validated['purpose_type'],
            'transfer_to' => $validated['transfer_to'] ?? null,
            'educational_status' => $validated['educational_status'],
            'educational_level' => $validated['educational_level'],
            'claim_mode' => $validated['claim_mode'],
            'representative_name' => $validated['representative_name'] ?? null,
            'others_specification' => $request->boolean('others') ? ($validated['others_specification'] ?? null) : null,
            'status' => DocumentRequestStatus::SUBMITTED->value,
            'submitted_at' => now(),
        ]);

        $documentRequest->documents()->attach($validated['document_ids']);

        SafeMailer::send($documentRequest->user, new DocumentRequestReceived($documentRequest));
        $documentRequest->user->notify(new DocumentRequestReceivedNotification($documentRequest));

        // A new request only becomes work when the registrar sees it. The
        // alert lands on their own queue, so the next click is one link away.
        RegistrarNotifier::alert(
            $documentRequest,
            'New document request',
            $documentRequest->student_name . ' submitted a request for ' . $documentRequest->documentsSummary() . '. It is waiting in your queue.'
        );

        return redirect()
            ->route('student.documents.index')
            ->with('swal', 'Request Successfully Submitted')
            ->with('status', 'Document request submitted. Track its progress under My Requests & Status.')
            ->with('instructions', $documentRequest->id);
    }

    /**
     * Student edit form for a request that has not been processed yet.
     */
    public function edit(DocumentRequest $documentRequest): View
    {
        Gate::authorize('update', $documentRequest);

        return view('student.documents.edit', [
            'documentRequest' => $documentRequest->load('documents'),
            'documents' => Document::active()->orderBy('name')->get(),
            'offices' => Office::toSelect(),
            'purposeTypes' => RequestPurposeType::toSelect(),
            'educationalStatuses' => EducationalStatus::toSelect(),
            'educationalLevels' => EducationalLevel::toSelect(),
            'claimModes' => ClaimMode::toSelectKeys(),
            'profile' => Auth::user()->studentProfile,
        ]);
    }

    public function update(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        Gate::authorize('update', $documentRequest);

        $validated = $this->validatedPayload($request);

        $documentRequest->update([
            'purpose_type' => $validated['purpose_type'],
            'transfer_to' => $validated['transfer_to'] ?? null,
            'educational_status' => $validated['educational_status'],
            'educational_level' => $validated['educational_level'],
            'claim_mode' => $validated['claim_mode'],
            'representative_name' => $validated['representative_name'] ?? null,
            'others_specification' => $request->boolean('others') ? ($validated['others_specification'] ?? null) : null,
        ]);

        $documentRequest->documents()->sync($validated['document_ids']);

        AuditLogger::log('request.updated', 'Student updated request ' . $documentRequest->request_number . '.');

        return redirect()
            ->route('student.documents.show', $documentRequest)
            ->with('status', 'Request updated successfully.');
    }

    public function cancel(DocumentRequest $documentRequest): RedirectResponse
    {
        Gate::authorize('cancel', $documentRequest);

        $documentRequest->forceFill([
            'status' => DocumentRequestStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ])->save();

        AuditLogger::log('request.cancelled', 'Student cancelled request ' . $documentRequest->request_number . '.');

        $documentRequest->user->notify(
            new DocumentRequestStatusNotification(
                $documentRequest,
                DocumentRequestStatus::CANCELLED->label(),
                'Your document request has been cancelled.'
            )
        );

        return redirect()
            ->route('student.documents.index')
            ->with('status', 'Request cancelled.');
    }

    public function destroy(DocumentRequest $documentRequest): RedirectResponse
    {
        Gate::authorize('delete', $documentRequest);

        AuditLogger::log('request.deleted', 'Student deleted request ' . $documentRequest->request_number . '.');

        $documentRequest->documents()->detach();
        $documentRequest->delete();

        return redirect()
            ->route('student.documents.index')
            ->with('status', 'Request deleted.');
    }

    private function validatedPayload(Request $request): array
    {
        foreach (['others_specification', 'transfer_to', 'representative_name'] as $field) {
            $value = $request->input($field);

            if (is_array($value)) {
                $request->merge([$field => $value[0] ?? null]);
            }
        }

        // Naming a document is what asks for it. When somebody writes the
        // document they came for, it counts as ticking Others whether or not
        // the box ever got ticked — so what they asked for is not quietly
        // dropped on save because a browser did not run our script.
        $typed = $request->input('others_specification');

        if (is_string($typed) && trim($typed) !== '') {
            $request->merge(['others' => 1]);
        }

        return $request->validate([
            'document_ids' => ['required', 'array', 'min:1'],
            'document_ids.*' => ['integer', 'exists:documents,id'],
            'others' => ['nullable', 'boolean'],
            'others_specification' => ['nullable', 'required_if:others,1', 'string', 'max:255'],
            'purpose_type' => ['required', Rule::in(RequestPurposeType::toSelectKeys())],
            'transfer_to' => ['nullable', 'required_if:purpose_type,transfer', 'string', 'max:500'],
            'educational_status' => ['required', Rule::in(EducationalStatus::toSelectKeys())],
            'educational_level' => ['required', Rule::in(EducationalLevel::toSelectKeys())],
            'claim_mode' => ['required', Rule::in(ClaimMode::toSelectKeys())],
            'representative_name' => ['nullable', 'required_if:claim_mode,representative', 'string', 'max:191'],
        ]);
    }

    /**
     * Show the tracking pipeline for a single request.
     */
    public function show(DocumentRequest $documentRequest): View
    {
        Gate::authorize('view', $documentRequest);

        // Encode a LAN-reachable URL so scanning the claim slip opens its tracking page.
        $payload = \App\Support\QrUrl::to('/verify/document/' . $documentRequest->claim_token);

        $qrCode = (new SvgWriter())->write(new QrCode($payload));

        return view('student.documents.show', [
            'documentRequest' => $documentRequest->load('documents'),
            'qrCodeDataUri' => $qrCode->getDataUri(),
        ]);
    }

    /**
     * Download the complete pass card — branding, student identity,
     * the transaction QR, reference number and verified footer.
     *
     * PNG by default: a phone cannot open an .svg, so the Android download
     * lands as an unusable "Couldn't open file". PNG opens in any gallery.
     *
     * ?format=svg still returns the vector original for print and scaling.
     */
    public function downloadQr(Request $request, DocumentRequest $documentRequest): \Symfony\Component\HttpFoundation\Response
    {
        Gate::authorize('view', $documentRequest);

        $user = Auth::user();
        $profile = $user->studentProfile;

        $options = [
            'sectionLabel' => 'Document Request QR',
            'refCode' => $documentRequest->request_number,
            'caption' => 'Present this QR code to authorized personnel.',
            'name' => $user->name,
            'studentId' => $profile?->student_id ?? '',
            'course' => $profile?->course ?? '',
            'yearLevel' => $profile?->year_level ?? '',
            'qrPayload' => \App\Support\QrUrl::to('/verify/document/' . $documentRequest->claim_token),
        ];

        if ($request->query('format') === 'svg') {
            return response(\App\Support\QrPassCard::svg($options))
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'attachment; filename="isufstpass-document-' . $documentRequest->request_number . '.svg"');
        }

        $png = \App\Support\QrPassCard::png($options);

        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Length', (string) strlen($png))
            ->header('Content-Disposition', 'attachment; filename="isufstpass-document-' . $documentRequest->request_number . '.png"');
    }

    /**
     * Printable official Requisition Form pre-filled with the request data.
     */
    public function requisition(DocumentRequest $documentRequest): View
    {
        Gate::authorize('view', $documentRequest);

        return view('student.documents.requisition-form', [
            'request' => $documentRequest->load(['user.studentProfile', 'documents']),
        ]);
    }
}
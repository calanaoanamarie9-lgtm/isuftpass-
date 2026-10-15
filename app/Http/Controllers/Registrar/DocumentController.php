<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        return view('registrar.documents.index', [
            'documents' => Document::query()->withCount('requests')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('documents', 'name')],
            'description' => ['nullable', 'string', 'max:500'],
            'fee' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'audience' => ['sometimes', Rule::in(array_keys(Document::audiences()))],
        ]);

        Document::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'fee' => $data['fee'],
            'audience' => $data['audience'] ?? Document::AUDIENCE_BOTH,
            'is_active' => true,
        ]);

        AuditLogger::log('document.created', 'Added document service '.$data['name'].' (₱'.number_format($data['fee'], 2).').');

        return back()->with('status', 'Document service added.');
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('documents', 'name')->ignore($document->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'fee' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'audience' => ['sometimes', Rule::in(array_keys(Document::audiences()))],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $document->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'fee' => $data['fee'],
            'audience' => $data['audience'] ?? $document->audience,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log('document.updated', 'Updated document service '.$document->name.'.');

        return back()->with('status', 'Document service updated.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        if ($document->requests()->exists()) {
            return back()->with('error', 'This document is attached to existing requests and cannot be deleted. Deactivate it instead.');
        }

        $document->delete();

        AuditLogger::log('document.deleted', 'Deleted document service '.$document->name.'.');

        return back()->with('status', 'Document service removed.');
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Enums\Office;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HelpController extends Controller
{
    /**
     * Guidelines, FAQs, and the office directory.
     *
     * This page is the Help & FAQs entry of every public account type, so the
     * questions follow the account: the two about document requests are only
     * asked by the accounts that can file one. Parents and guests book
     * appointments and nothing else, and a question about tracking a request
     * they cannot submit only misleads them.
     */
    public function index(): View
    {
        $faqs = [
            [
                'q' => 'How do I book an appointment?',
                'a' => 'Go to New Request and choose Book Appointment. Pick your office, purpose, preferred date, and time slot, then submit. You will receive a confirmation email and an in-app notification.',
            ],
            [
                'q' => 'How many appointments can I book?',
                'a' => 'You can hold one active booking per office per day. Time slots have limited seats, so book early.',
            ],
        ];

        if (Auth::user()?->canRequestDocuments()) {
            $faqs[] = [
                'q' => 'How do I request a document?',
                'a' => 'Go to New Request and choose Request Document. Select the document, enter your purpose and mode of claiming, then attach supporting files if required.',
            ];

            $faqs[] = [
                'q' => 'How do I track my document request?',
                'a' => 'Open My Requests to see the processing pipeline: Submitted, Verified, In Progress, Ready for Claiming, and Completed. Status changes appear as notifications.',
            ];
        }

        $faqs = array_merge($faqs, [
            [
                'q' => 'Can I cancel or reschedule an appointment?',
                'a' => 'Yes, on the My Appointments page as long as the appointment is still upcoming and has not passed the office cut-off.',
            ],
            [
                'q' => 'What is my QR Pass for?',
                'a' => 'Your Digital Student ID / QR Pass is scanned at the university gate for entry verification. Open it from the sidebar before you arrive.',
            ],
        ]);

        return view('student.help.index', [
            'offices' => Office::cases(),
            'faqs' => $faqs,
        ]);
    }
}
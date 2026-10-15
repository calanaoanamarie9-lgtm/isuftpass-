<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'name' => 'Transcript of Record (TOR)',
                'description' => 'Official consolidated record of all academic subjects and grades taken.',
                'fee' => 100.00,
                'audience' => Document::AUDIENCE_BOTH,
            ],
            [
                'name' => 'Certificate of Grades (COG)',
                'description' => 'Official certification of the grades a student earned in a term or school year.',
                'fee' => 60.00,
                'audience' => Document::AUDIENCE_STUDENT,
            ],
            [
                'name' => 'Certification / CAVS',
                'description' => 'Certification of a student record or CAVS verification of signature.',
                'fee' => 60.00,
                'audience' => Document::AUDIENCE_BOTH,
            ],
            [
                'name' => 'Diploma',
                'description' => 'Official diploma issued to graduates. Replacement copies are issued to alumni.',
                'fee' => 250.00,
                'audience' => Document::AUDIENCE_ALUMNI,
            ],
            [
                'name' => 'Transfer Credentials',
                'description' => 'Credentials issued to students transferring to another school.',
                'fee' => 100.00,
                'audience' => Document::AUDIENCE_BOTH,
            ],
            [
                'name' => 'Permanent Record (F-137-A)',
                'description' => 'Permanent academic record of the student (Form 137-A).',
                'fee' => 150.00,
                'audience' => Document::AUDIENCE_STUDENT,
            ],
            [
                'name' => 'Good Moral Character',
                'description' => 'Certifies that the student is of good moral character.',
                'fee' => 50.00,
                'audience' => Document::AUDIENCE_STUDENT,
            ],
        ];

        Document::whereNotIn('name', array_column($documents, 'name'))->delete();

        foreach ($documents as $document) {
            Document::updateOrCreate(
                ['name' => $document['name']],
                $document
            );
        }
    }
}

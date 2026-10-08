<?php

namespace App\Enums;

enum Office: string
{
    case OSAS = 'OSAS';
    case Registrar = 'Registrar';
    case Guidance = 'Guidance';
    case Cashier = 'Cashier';
    case Accounting = 'Accounting';
    case Admin = 'Admin';
    case Library = 'Library';
    case Cici = 'CICI';
    case CBMSD = 'CBMSD';
    case Coag = 'COAG';
    case Coed = 'COED';

    /**
     * Options formatted for HTML select inputs.
     *
     * The enum's built-in offices come first; any self-registered office
     * whose account was approved by an admin is merged in after them, so a
     * new office becomes bookable the moment it is accepted — no deploy
     * needed. Validation rules built from toSelectKeys() accept them too.
     */
    public static function toSelect(): array
    {
        $options = collect(self::cases())
            ->mapWithKeys(fn (self $office) => [$office->value => $office->value]);

        foreach (self::registeredOffices() as $name) {
            $options->put($name, $name);
        }

        return $options->all();
    }

    public static function toSelectKeys(): array
    {
        return array_keys(self::toSelect());
    }

    /**
     * Names of self-registered offices with an approved, active account.
     * Resolved once per request; falls back to the enum alone when the
     * database is not reachable (migrations, early console boot).
     *
     * @return list<string>
     */
    private static function registeredOffices(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        try {
            $cache = \App\Models\User::query()
                ->where('role', \App\Models\User::ROLE_OFFICE)
                ->where('approval_status', \App\Models\User::APPROVAL_APPROVED)
                ->where('is_active', true)
                ->whereNotNull('office')
                ->distinct()
                ->orderBy('office')
                ->pluck('office')
                ->all();
        } catch (\Throwable) {
            $cache = [];
        }

        return $cache;
    }

    /**
     * Resolve an office name typed by an applicant to the office it names.
     *
     * The registration form no longer offers this list - the applicant types
     * their own office, which may not exist here yet - so "library", "LIBRARY"
     * and "University Library" all come back as Library. That matters because
     * appointments, consultation services and availability are keyed by the
     * value below: an account holding the label instead would be looking at an
     * office with no records. A name that matches nothing is returned exactly
     * as typed, with runs of spaces collapsed - a new office is allowed to
     * apply before it has a case here.
     */
    public static function fromTyped(string $typed): string
    {
        $typed = trim((string) preg_replace('/\s+/u', ' ', $typed));

        foreach (self::cases() as $office) {
            if (strcasecmp($typed, $office->value) === 0 || strcasecmp($typed, $office->label()) === 0) {
                return $office->value;
            }
        }

        return $typed;
    }

    public function label(): string
    {
        return match ($this) {
            self::OSAS => 'Office of Student Affairs & Services',
            self::Registrar => 'Office of the Registrar',
            self::Guidance => 'Guidance & Counseling Office',
            self::Cashier => 'Cashier Office',
            self::Accounting => 'Accounting Office',
            self::Admin => 'Office of the Administrator',
            self::Library => 'University Library',
                self::Cici => 'College of Informatics and Computing Innovations',
                self::CBMSD => 'College of Business, Management, and Development Studies',
                self::Coag => 'College of Agriculture',
                self::Coed => 'College of Education',
        };
    }

    public function details(): array
    {
        return [
            'description' => match ($this) {
                self::OSAS => 'Student services, activities, and welfare concerns.',
                self::Registrar => 'Student records, grades, transcripts, and document issuance.',
                self::Guidance => 'Counseling services, testing, and student development.',
                self::Cashier => 'Financial transactions and official receipts for fees.',
                self::Accounting => 'Billing, accounts, and fund-related concerns.',
                self::Admin => 'General inquiries and administrative matters.',
                self::Library => 'Library services, borrowing, references, and study resources.',
                self::Cici => 'Department consultations, academic concerns, and college transactions.',
                self::CBMSD => 'Business management consultations, academic advising, and college transactions.',
                self::Coag => 'Agricultural consultations, academic advising, and college transactions.',
                self::Coed => 'Education consultations, academic advising, and college transactions.',
            },
            'location' => match ($this) {
                self::OSAS => 'Student Center, 2nd Floor',
                self::Registrar => 'Main Building, Ground Floor',
                self::Guidance => 'Main Building, 2nd Floor',
                self::Cashier => 'Finance Building, Ground Floor',
                self::Accounting => 'Finance Building, 2nd Floor',
                self::Admin => 'Main Building, 3rd Floor',
                self::Library => 'Library Building, Ground Floor',
                self::Cici => 'CICI Building, Ground Floor',
                self::CBMSD => 'CBMSD Building, Ground Floor',
                self::Coag => 'COAG Building, Ground Floor',
                self::Coed => 'COED Building, Ground Floor',
            },
            'hours' => 'Mon - Fri, 8:00 AM - 5:00 PM',
        ];
    }
}
<?php

namespace App\Enums;

use App\Models\User;

enum Office: string
{
    case SSC = 'SSC';
    case Registrar = 'Registrar';
    case Cashier = 'Cashier';
    case Accounting = 'Accounting';
    case Library = 'Library';
    case Cici = 'CICI';
    case CBMSD = 'CBMSD';
    case Coag = 'COAG';
    case Coed = 'COED';

    /**
     * Options formatted for HTML select inputs.
     *
     * The enum's built-in nine offices come first. Self-registered offices
     * (e.g. Property Management) are merged in after them so a new office
     * becomes bookable the moment an admin approves it — no deploy needed.
     *
     * Legacy office names (OSAS, Admin, Guidance) are explicitly excluded
     * even if an old account row still carries one of those values, so the
     * rename to SSC does not surface the old name in any form or gate.
     */
    public static function toSelect(): array
    {
        $builtin = collect(self::cases())
            ->mapWithKeys(fn (self $office) => [$office->value => $office->label()]);

        // Values that must never appear in the dropdown regardless of what
        // is stored in the users table.
        $deprecated = ['OSAS', 'Admin', 'Guidance'];

        foreach (self::registeredOffices() as $name) {
            if (! $builtin->has($name) && ! in_array($name, $deprecated, true)) {
                $builtin->put($name, $name);
            }
        }

        return $builtin->all();
    }

    public static function toSelectKeys(): array
    {
        return array_keys(self::toSelect());
    }

    /**
     * Offices a student can book an appointment with.
     *
     * Every office is bookable except the Cashier's, which is a payment
     * counter rather than an appointment desk: a student settles fees at the
     * counter, and the document-request flow is what sends them there. The
     * Cashier stays in toSelect() — it is still an office a document request,
     * a visitor's destination can name.
     */
    public static function bookable(): array
    {
        return array_diff_key(self::toSelect(), [self::Cashier->value => true]);
    }

    /**
     * @return list<string>
     */
    public static function bookableKeys(): array
    {
        return array_keys(self::bookable());
    }

    /**
     * Offices that do not ask the student for a time when they book.
     *
     * A student booking one of these picks a date only; the office answers
     * with the exact time when it approves the appointment, so the booking
     * form hides the slot picker. The Registrar runs its queue this way.
     */
    public static function setsTimeOnApproval(string $office): bool
    {
        return $office === self::Registrar->value;
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
            $cache = User::query()
                ->where('role', User::ROLE_OFFICE)
                ->where('approval_status', User::APPROVAL_APPROVED)
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

        // Map legacy / renamed office identifiers to their current enum value
        // so that old appointment rows, seeder calls, and typed registrations
        // still resolve correctly after the rename.
        $aliases = [
            'OSAS' => self::SSC->value,
            'Office of Student Affairs & Services' => self::SSC->value,
            'Office of Student Affairs and Services' => self::SSC->value,
        ];

        foreach ($aliases as $alias => $canonical) {
            if (strcasecmp($typed, $alias) === 0) {
                return $canonical;
            }
        }

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
            self::SSC => 'Student Support Center (SSC)',
            self::Registrar => "Registrar's Office",
            self::Cashier => "Cashier's Office",
            self::Accounting => 'Accounting Office',
            self::Library => 'Library Office',
            self::Cici => 'College of Informatics and Computing Innovation (CICI)',
            self::CBMSD => 'College of Business Management and Sustainable Development (CBMSD)',
            self::Coag => 'College of Agriculture (COAG)',
            self::Coed => 'College of Education (COED)',
        };
    }

    public function details(): array
    {
        return [
            'description' => match ($this) {
                self::SSC => 'Student services, activities, and welfare concerns.',
                self::Registrar => 'Student records, grades, transcripts, and document issuance.',
                self::Cashier => 'Financial transactions and official receipts for fees.',
                self::Accounting => 'Billing, accounts, and fund-related concerns.',
                self::Library => 'Library services, borrowing, references, and study resources.',
                self::Cici => 'Department consultations, academic concerns, and college transactions.',
                self::CBMSD => 'Business management consultations, academic advising, and college transactions.',
                self::Coag => 'Agricultural consultations, academic advising, and college transactions.',
                self::Coed => 'Education consultations, academic advising, and college transactions.',
            },
            'location' => match ($this) {
                self::SSC => 'Student Center, 2nd Floor',
                self::Registrar => 'Main Building, Ground Floor',
                self::Cashier => 'Finance Building, Ground Floor',
                self::Accounting => 'Finance Building, 2nd Floor',
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

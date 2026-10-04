<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Covers php artisan mail:test, the smoke check used to tell a missing key,
 * a refused sender and a working transport apart.
 *
 * All three cases return before any network call: phpunit pins MAIL_MAILER
 * to "array", and each test either fails validation up front or deliberately
 * sends through that in-memory transport. A test that reached Resend would
 * be both flaky and a live call on the account quota.
 */
class MailTestCommandTest extends TestCase
{
    public function test_missing_api_key_is_reported_before_anything_is_sent(): void
    {
        config([
            'mail.default' => 'resend',
            'services.resend.key' => '',
        ]);

        $this->artisan('mail:test')
            ->expectsOutputToContain('api key : NOT SET')
            ->expectsOutputToContain('RESEND_API_KEY is empty')
            ->assertExitCode(1);
    }

    public function test_missing_sender_is_reported_before_anything_is_sent(): void
    {
        config([
            'mail.from.address' => '',
        ]);

        $this->artisan('mail:test')
            ->expectsOutputToContain('MAIL_FROM_ADDRESS is empty')
            ->assertExitCode(1);
    }

    public function test_a_working_transport_reports_acceptance(): void
    {
        config([
            'mail.default' => 'array',
            'mail.from.address' => 'no-reply@isufst.edu.ph',
            'services.resend.key' => 'not-used-by-the-array-transport',
        ]);

        $this->artisan('mail:test', ['--to' => 'someone@isufst.edu.ph'])
            ->expectsOutputToContain('Sending to someone@isufst.edu.ph')
            ->expectsOutputToContain('ACCEPTED')
            ->assertExitCode(0);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

/**
 * Covers the Brevo transport - the mailer this app switched to because
 * Render blocks outbound SMTP and Resend cannot address an arbitrary
 * recipient until isufst.edu.ph is domain-verified.
 *
 * Every request is faked: phpunit pins MAIL_MAILER to "array" for the rest
 * of the suite, and a stray live call would be both flaky and a charge
 * against the free quota (300 emails/day).
 */
class BrevoTransportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'brevo',
            'services.brevo.key' => 'brv-test-key',
            'mail.from.address' => 'isufstpass@gmail.com',
            'mail.from.name' => 'ISUFSTPASS',
        ]);
    }

    public function test_the_message_reaches_the_api_with_the_key_and_a_body(): void
    {
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'abc-123'], 201),
        ]);

        Mail::raw('Your document is ready for pickup.', function ($message) {
            $message->to('juan@isufst.edu.ph')->subject('Document ready');
        });

        Http::assertSent(function ($request) {
            $headers = array_change_key_case($request->headers());
            $payload = $request->data();

            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && ($headers['api-key'][0] ?? null) === 'brv-test-key'
                && $payload['sender']['email'] === 'isufstpass@gmail.com'
                && $payload['sender']['name'] === 'ISUFSTPASS'
                && $payload['to'][0]['email'] === 'juan@isufst.edu.ph'
                && $payload['subject'] === 'Document ready'
                && $payload['textContent'] === 'Your document is ready for pickup.'
                // A text-only message must not claim to carry HTML.
                && ! isset($payload['htmlContent']);
        });
    }

    public function test_carbon_copies_are_split_out_of_the_to_list(): void
    {
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'abc-123'], 201),
        ]);

        Mail::raw('Copy of the request.', function ($message) {
            $message->to('student@isufst.edu.ph')
                ->cc('registrar@isufst.edu.ph')
                ->bcc('audit@isufst.edu.ph')
                ->subject('Copied');
        });

        Http::assertSent(function ($request) {
            $payload = $request->data();

            $addresses = static fn (string $key): array => array_column($payload[$key] ?? [], 'email');

            return $addresses('to') === ['student@isufst.edu.ph']
                && $addresses('cc') === ['registrar@isufst.edu.ph']
                && $addresses('bcc') === ['audit@isufst.edu.ph'];
        });
    }

    public function test_a_rejected_request_reports_what_brevo_said(): void
    {
        Http::fake([
            'api.brevo.com/*' => Http::response(['message' => 'The API key passed was not valid'], 401),
        ]);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('The API key passed was not valid');

        Mail::raw('Anything', function ($message) {
            $message->to('student@isufst.edu.ph')->subject('Anything');
        });
    }

    public function test_a_message_with_no_body_is_refused_before_the_network_is_touched(): void
    {
        Http::fake();

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('neither an HTML nor a text body');

        Mail::raw('', function ($message) {
            $message->to('student@isufst.edu.ph')->subject('Empty');
        });
    }
}

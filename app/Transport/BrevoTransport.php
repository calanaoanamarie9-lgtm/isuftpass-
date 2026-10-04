<?php

namespace App\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * Deliver one message through Brevo's transactional API over HTTPS.
 *
 * Render's free tier blocks outbound SMTP (ports 25/465/587), so the SMTP
 * relay Brevo also publishes can never be reached from this host. The HTTP
 * API only needs an `api-key` header, and the free tier does not block it.
 *
 * Laravel ships transports for smtp, mailgun, postmark, ses and resend but
 * not for Brevo, so this one is registered by AppServiceProvider.
 */
class BrevoTransport extends AbstractTransport
{
    /**
     * Headers Symfony generates while serialising a message. Brevo builds
     * its own copy of each of them, so forwarding these would only fight
     * with what the API already does; anything else is a custom header the
     * application meant to send and is passed straight through.
     */
    private const GENERATED_HEADERS = [
        'from',
        'to',
        'cc',
        'bcc',
        'reply-to',
        'sender',
        'subject',
        'content-type',
        'date',
        'message-id',
        'mime-version',
        'return-path',
    ];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $endpoint = 'https://api.brevo.com',
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
            ])
                ->timeout(20)
                ->post(
                    $this->endpoint.'/v3/smtp/email',
                    $this->payload($email, $message->getEnvelope()),
                );
        } catch (\Throwable $e) {
            throw new TransportException(
                'Brevo API could not be reached. Reason: '.$e->getMessage(),
                0,
                $e,
            );
        }

        if ($response->failed()) {
            // Brevo explains *why* in the body ("The API key passed was not
            // valid", "The sender ... does not exist"), which is the whole
            // point of reading it instead of only the status code.
            throw new TransportException(sprintf(
                'Request to Brevo API failed with HTTP %d. Reason: %s',
                $response->status(),
                $response->json('message') ?? $response->body(),
            ));
        }

        $email->getHeaders()->addHeader('X-Brevo-Email-ID', (string) $response->json('messageId', ''));
    }

    /**
     * Shape one Symfony message as the body Brevo's /v3/smtp/email expects.
     *
     * @return array<string, mixed>
     */
    private function payload(Email $email, Envelope $envelope): array
    {
        $payload = [
            'sender' => self::person($envelope->getSender()),
            'to' => self::people($this->addressees($email, $envelope)),
            'subject' => (string) $email->getSubject(),
        ];

        // Empty recipient lists are valid in Symfony and meaningless to
        // Brevo, which rejects an empty array rather than ignoring it.
        if ($cc = self::people($email->getCc())) {
            $payload['cc'] = $cc;
        }

        if ($bcc = self::people($email->getBcc())) {
            $payload['bcc'] = $bcc;
        }

        if ($replyTo = $email->getReplyTo()) {
            $payload['replyTo'] = self::person($replyTo[0]);
        }

        $payload += array_filter([
            'htmlContent' => self::body($email->getHtmlBody()),
            'textContent' => self::body($email->getTextBody()),
        ]);

        if (! isset($payload['htmlContent']) && ! isset($payload['textContent'])) {
            throw new TransportException('Brevo refuses a message with neither an HTML nor a text body.');
        }

        if ($headers = $this->headers($email)) {
            $payload['headers'] = $headers;
        }

        if ($attachments = $this->attachments($email)) {
            $payload['attachment'] = $attachments;
        }

        return $payload;
    }

    /**
     * The envelope carries To, Cc and Bcc together, but Brevo wants them
     * split; anything already named in Cc or Bcc must leave the To list.
     *
     * @return list<Address>
     */
    private function addressees(Email $email, Envelope $envelope): array
    {
        $carbonCopies = array_map(
            static fn (Address $address) => strtolower($address->getAddress()),
            array_merge($email->getCc(), $email->getBcc()),
        );

        return array_values(array_filter(
            $envelope->getRecipients(),
            static fn (Address $address) => ! in_array(strtolower($address->getAddress()), $carbonCopies, true),
        ));
    }

    /**
     * @param  list<Address>  $addresses
     * @return list<array<string, string>>
     */
    private static function people(array $addresses): array
    {
        return array_map(
            static fn (Address $address) => self::person($address),
            $addresses,
        );
    }

    /**
     * @return array<string, string>
     */
    private static function person(Address $address): array
    {
        return $address->getName() === ''
            ? ['email' => $address->getAddress()]
            : ['email' => $address->getAddress(), 'name' => $address->getName()];
    }

    /**
     * A body streamed from a view arrives as an open handle; Brevo only
     * accepts a string. An absent body is reported as null so that the
     * caller can tell "empty" from "present but blank".
     */
    private static function body(mixed $body): ?string
    {
        if (is_resource($body)) {
            $body = stream_get_contents($body);
        }

        return is_string($body) && $body !== '' ? $body : null;
    }

    /**
     * @return array<string, string>
     */
    private function headers(Email $email): array
    {
        $headers = [];

        foreach ($email->getHeaders()->all() as $name => $header) {
            if (in_array(strtolower($name), self::GENERATED_HEADERS, true)) {
                continue;
            }

            $headers[$header->getName()] = $header->getBodyAsString();
        }

        return $headers;
    }

    /**
     * Brevo takes attachments base64-encoded, one entry per file.
     *
     * @return list<array<string, string>>
     */
    private function attachments(Email $email): array
    {
        return array_map(static function ($attachment) {
            $headers = $attachment->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');

            return [
                'name' => $filename ?: 'attachment',
                'content' => base64_encode($attachment->bodyToString()),
            ];
        }, $email->getAttachments());
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'brevo';
    }
}

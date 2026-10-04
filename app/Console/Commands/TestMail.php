<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Send one test message through whatever the mailer the app is configured to
 * use, and report exactly what happened.
 *
 *     php artisan mail:test
 *     php artisan mail:test --to=you@gmail.com
 *
 * Each API transport answers a bad key with an HTTP error carrying its own
 * explanation, so this prints the transport's words rather than a bare
 * "failed". A missing key, a sender the key is not allowed to use, and an
 * exhausted daily quota all look identical from the app's side if you only
 * look at pass/fail.
 *
 * Config is read through config(), never env(): on Render the values are
 * captured at boot and env() returns null once the config is cached, which
 * would make this report "not set" for a key that is in fact present.
 */
class TestMail extends Command
{
    protected $signature = 'mail:test
        {--to= : Recipient address (defaults to MAIL_FROM_ADDRESS)}';

    protected $description = 'Send a test email and report whether the transport accepted it';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $from = trim((string) config('mail.from.address'));
        $fromName = (string) config('mail.from.name');

        // Both API transports keep their secret under config/services.php,
        // and each has its own wording for "why did this not send". Unknown
        // mailers (array, log, smtp) fall through to the Resend wording.
        $provider = match ($mailer) {
            'brevo' => [
                'config' => 'services.brevo.key',
                'secret' => 'BREVO_API_KEY',
                'source' => 'https://app.brevo.com -> Settings -> SMTP & API',
                'logs' => 'Brevo -> Transactional -> Emails',
            ],
            default => [
                'config' => 'services.resend.key',
                'secret' => 'RESEND_API_KEY',
                'source' => 'https://resend.com -> API Keys',
                'logs' => 'Resend -> Logs',
            ],
        };

        $key = trim((string) config($provider['config']));

        $this->info('Mail configuration');
        $this->line('  mailer  : '.$mailer);
        $this->line('  from    : '.($fromName !== '' ? $fromName.' <'.$from.'>' : $from));
        $this->line('  api key : '.($key === '' ? 'NOT SET' : 'set ('.strlen($key).' chars, hidden)'));
        $this->newLine();

        // --- Config that can never work, reported before anything is sent ----

        if ($from === '') {
            $this->error('MAIL_FROM_ADDRESS is empty - no provider accepts a message with no sender.');

            return self::FAILURE;
        }

        if (in_array($mailer, ['resend', 'brevo'], true) && $key === '') {
            $this->error($provider['secret'].' is empty, so no message can be sent.');

            $this->newLine();
            $this->line('Set it where the app actually reads it:');
            $this->line('  Render : Dashboard -> this service -> Environment -> add '.$provider['secret']);
            $this->line('           value comes from '.$provider['source']);
            $this->line('           (render.yaml marks it sync: false so Blueprint cannot revert it)');
            $this->line('  Local  : .env -> '.$provider['secret'].'=... then  php artisan config:clear');
            $this->newLine();
            $this->line('Then run this command again.');

            return self::FAILURE;
        }

        // --- Senders that work but look different in the recipient's inbox ---

        if ($from === 'onboarding@resend.dev') {
            $this->warn('onboarding@resend.dev is Resend\'s shared trial sender: fine for this smoke test,');
            $this->warn('but verify your own domain in Resend before mail goes to real student addresses.');
            $this->newLine();
        }

        if ($mailer === 'brevo' && str_ends_with(strtolower($from), '@gmail.com')) {
            $this->warn('Gmail cannot be domain-authenticated, so Brevo rewrites the from address to a');
            $this->warn('compliant one (something@....t-sender-sib.com). Mail still arrives - expected, not a fault.');
            $this->newLine();
        }

        $to = trim((string) $this->option('to')) ?: $from;

        $this->line('Sending to '.$to.' ...');

        $started = microtime(true);

        try {
            Mail::raw(
                'ISUFSTPASS mail test'.PHP_EOL.PHP_EOL
                .'Sent at  '.now()->toDateTimeString().PHP_EOL
                .'Mailer   '.$mailer.PHP_EOL
                .'From     '.$from.PHP_EOL
                .'To       '.$to.PHP_EOL.PHP_EOL
                .'If you can read this in your inbox, delivery works end to end.'.PHP_EOL,
                function ($message) use ($to, $from, $fromName) {
                    $message->from($from, $fromName)
                        ->to($to)
                        ->subject('ISUFSTPASS mail test');
                }
            );
        } catch (\Throwable $e) {
            $elapsed = (int) round((microtime(true) - $started) * 1000);

            $this->newLine();
            $this->error("FAILED after {$elapsed} ms");
            $this->line('  '.$e->getMessage());

            for ($previous = $e->getPrevious(); $previous !== null; $previous = $previous->getPrevious()) {
                $this->line('  caused by: '.$previous->getMessage());
            }

            $this->newLine();
            $this->line('Most common causes, in order:');

            foreach ($this->commonCauses($mailer) as $cause) {
                $this->line('  '.$cause);
            }

            return self::FAILURE;
        }

        $elapsed = (int) round((microtime(true) - $started) * 1000);

        $this->newLine();
        $this->info("ACCEPTED after {$elapsed} ms");
        $this->line('  To: '.$to);
        $this->newLine();
        $this->line('  Accepted only means the API took the message. The proof is in');
        $this->line('  '.$provider['logs'].', which will show delivered / bounced / failed.');

        return self::SUCCESS;
    }

    /**
     * The failures that look identical from the app's side unless you have
     * read what the provider actually said.
     *
     * @return list<string>
     */
    private function commonCauses(string $mailer): array
    {
        if ($mailer === 'brevo') {
            return [
                '1. BREVO_API_KEY is wrong, revoked, or was not picked up (restart after setting it)',
                '2. MAIL_FROM_ADDRESS is not a verified sender in Brevo (Settings -> Senders)',
                '3. free-tier quota spent: 300 emails/day, 9,000/month',
            ];
        }

        return [
            '1. key is wrong, revoked, or was not picked up (restart after setting it)',
            '2. this sender address is not allowed for that key',
            '3. free-tier quota spent: 100 emails/day, 3,000/month',
        ];
    }
}

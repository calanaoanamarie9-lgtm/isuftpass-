<?php

namespace Tests\Unit;

use App\Support\QrPayload;
use Tests\TestCase;

/**
 * The one reader every desk scans through: whatever the registrar's camera,
 * an office's USB scanner or the gate hands it, the same value must come
 * back as the same identifier — or as nothing at all.
 */
class QrPayloadTest extends TestCase
{
    private const UUID = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

    public function test_a_printed_url_is_read_as_the_token_it_carries(): void
    {
        foreach (['pass', 'document', 'appointment', 'visitor'] as $segment) {
            $this->assertSame(
                self::UUID,
                QrPayload::token('https://isuftpass.test/verify/'.$segment.'/'.self::UUID)
            );
        }
    }

    public function test_each_url_names_its_own_kind_of_pass(): void
    {
        $this->assertSame(
            ['pass' => self::UUID],
            array_filter(QrPayload::extract('https://isuftpass.test/verify/pass/'.self::UUID))
        );

        $this->assertSame(
            ['claim' => self::UUID],
            array_filter(QrPayload::extract('https://isuftpass.test/verify/document/'.self::UUID))
        );

        $this->assertSame(
            ['appointment' => self::UUID],
            array_filter(QrPayload::extract('https://isuftpass.test/verify/appointment/'.self::UUID))
        );

        $this->assertSame(
            ['visitor' => self::UUID],
            array_filter(QrPayload::extract('https://isuftpass.test/verify/visitor/'.self::UUID))
        );
    }

    public function test_a_bare_token_and_a_typed_reference_both_carry_through(): void
    {
        // A USB scanner appends a carriage return to whatever it reads.
        $this->assertSame(self::UUID, QrPayload::token(self::UUID."\r\n"));

        $this->assertSame('APT-2026-ABCD', QrPayload::token('APT-2026-ABCD'));

        $this->assertSame(
            ['appointment' => 'APT-2026-ABCD'],
            array_filter(QrPayload::extract('https://isuftpass.test/verify/appointment/APT-2026-ABCD'))
        );
    }

    public function test_a_legacy_json_payload_is_read_like_the_url_it_replaced(): void
    {
        $this->assertSame(
            ['pass' => self::UUID],
            array_filter(QrPayload::extract(json_encode(['type' => 'isufstpass', 'token' => self::UUID])))
        );

        $this->assertSame(
            ['claim' => self::UUID],
            array_filter(QrPayload::extract(json_encode(['type' => 'isufstdoc', 'request' => self::UUID])))
        );
    }

    public function test_plain_text_is_never_mistaken_for_a_token(): void
    {
        $typed = [
            'not-a-real-pass',
            'Juan Dela Cruz',
            'juan.delacruz@isuftpass.test',
            '/verify/pass/nope',
        ];

        foreach ($typed as $text) {
            $this->assertSame('', QrPayload::token($text), $text);
            $this->assertFalse(QrPayload::looksLikeToken($text), $text);
        }

        $this->assertTrue(QrPayload::looksLikeToken('https://isuftpass.test/verify/pass/'.self::UUID));
    }
}

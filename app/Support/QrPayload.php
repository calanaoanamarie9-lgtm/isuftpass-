<?php

namespace App\Support;

/**
 * What a scanned value means.
 *
 * The same pass is presented to the registrar's station, to an office desk
 * and to the gate, and every one of them receives it the same way: a phone
 * camera's full verification URL, a USB scanner's bare token or typed
 * reference number, or a legacy JSON payload printed by an older build.
 * Reading it belongs in one place so a desk cannot silently understand a
 * different set of codes than the desk next door.
 */
class QrPayload
{
    /**
     * The five things a scan can carry. At most one is ever set — the first
     * pattern that matches wins, exactly as the printed code means it.
     *
     * `visitor` is the gate's own walk-in pass and `reference` is a booking
     * number typed by hand: neither is a transaction the registrar or an
     * office desk resolves, but the gate reads both through here too, so the
     * whole platform still shares one reader.
     *
     * @return array{
     *     pass: string|null,
     *     claim: string|null,
     *     appointment: string|null,
     *     visitor: string|null,
     *     reference: string|null
     * }
     */
    public static function extract(string $value): array
    {
        $value = trim($value);
        $uuid = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

        $tokens = [
            'pass' => null,
            'claim' => null,
            'appointment' => null,
            'visitor' => null,
            'reference' => null,
        ];

        if ($value === '') {
            return $tokens;
        }

        if (preg_match("#/verify/pass/({$uuid})#i", $value, $m)) {
            $tokens['pass'] = $m[1];
        } elseif (preg_match("#/verify/document/({$uuid})#i", $value, $m)) {
            $tokens['claim'] = $m[1];
        } elseif (preg_match("#/verify/appointment/({$uuid})#i", $value, $m)) {
            $tokens['appointment'] = $m[1];
        } elseif (preg_match("#/verify/visitor/({$uuid})#i", $value, $m)) {
            // The gate's walk-in pass: no booking or transaction sits behind
            // it, but it is still a code the scanner must read.
            $tokens['visitor'] = $m[1];
        } elseif (preg_match('#/verify/appointment/([^/]+)#i', $value, $m)) {
            // …and a reference-form payload, /verify/appointment/APT-2026-ABCD,
            // the same way the printed card names the booking.
            $tokens['appointment'] = $m[1];
        } elseif (preg_match("/^{$uuid}$/i", $value)) {
            // A bare token has no type of its own: the caller looks it up as
            // an appointment first and as an identity pass second.
            $tokens['pass'] = $value;
        } elseif (preg_match('/^APT-[A-Z0-9]+-[A-Z0-9]+$/i', $value)) {
            // The reference number printed on a booking, typed into the gate
            // when the card itself will not scan.
            $tokens['reference'] = $value;
        } else {
            $payload = json_decode($value, true);

            if (is_array($payload)) {
                $type = $payload['type'] ?? null;

                if ($type === 'isufstpass' && isset($payload['token'])) {
                    $tokens['pass'] = (string) $payload['token'];
                }

                if ($type === 'isufstdoc' && isset($payload['request'])) {
                    $tokens['claim'] = (string) $payload['request'];
                }
            }
        }

        return $tokens;
    }

    /**
     * The one identifier a scan carries, whatever printed it — the gate and
     * the office's check-in desk resolve it without caring which pass it was.
     * Empty when the value names nothing: a typo, a name, an email address.
     */
    public static function token(string $value): string
    {
        foreach (self::extract($value) as $token) {
            if ($token !== null) {
                return $token;
            }
        }

        return '';
    }

    /**
     * True when the value names a token at all — as opposed to a typo, a name
     * or an email address typed into the same box.
     */
    public static function looksLikeToken(string $value): bool
    {
        return self::token($value) !== '';
    }
}

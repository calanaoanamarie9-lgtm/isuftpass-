<?php

namespace App\Support;

/**
 * What a scanned value means.
 *
 * The same pass is presented to the registrar's station, to an office desk
 * and to the gate, and every one of them receives it the same way: a phone
 * camera's full verification URL, a USB scanner's bare token, or a legacy
 * JSON payload printed by an older build. Reading it belongs in one place so
 * a desk cannot silently understand a different set of codes than the desk
 * next door.
 */
class QrPayload
{
    /**
     * @return array{pass: string|null, claim: string|null, appointment: string|null}
     */
    public static function extract(string $value): array
    {
        $value = trim($value);
        $uuid = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

        $none = ['pass' => null, 'claim' => null, 'appointment' => null];

        if ($value === '') {
            return $none;
        }

        if (preg_match("#/verify/pass/({$uuid})#i", $value, $m)) {
            return ['pass' => $m[1], 'claim' => null, 'appointment' => null];
        }

        if (preg_match("#/verify/document/({$uuid})#i", $value, $m)) {
            return ['pass' => null, 'claim' => $m[1], 'appointment' => null];
        }

        if (preg_match("#/verify/appointment/({$uuid})#i", $value, $m)) {
            return ['pass' => null, 'claim' => null, 'appointment' => $m[1]];
        }

        // …and a reference-form payload, /verify/appointment/APT-2026-ABCD,
        // the same way the printed card names the booking.
        if (preg_match('#/verify/appointment/([^/]+)#i', $value, $m)) {
            return ['pass' => null, 'claim' => null, 'appointment' => $m[1]];
        }

        // A bare token has no type of its own: the caller looks it up as an
        // appointment first and as an identity pass second.
        if (preg_match("/^{$uuid}$/i", $value)) {
            return ['pass' => $value, 'claim' => null, 'appointment' => null];
        }

        $payload = json_decode($value, true);

        if (is_array($payload)) {
            $type = $payload['type'] ?? null;

            if ($type === 'isufstpass' && isset($payload['token'])) {
                return ['pass' => (string) $payload['token'], 'claim' => null, 'appointment' => null];
            }

            if ($type === 'isufstdoc' && isset($payload['request'])) {
                return ['pass' => null, 'claim' => (string) $payload['request'], 'appointment' => null];
            }
        }

        return $none;
    }

    /**
     * True when the value names a token at all — as opposed to a typo, a name
     * or an email address typed into the same box.
     */
    public static function looksLikeToken(string $value): bool
    {
        $tokens = self::extract($value);

        return $tokens['pass'] !== null || $tokens['claim'] !== null || $tokens['appointment'] !== null;
    }
}

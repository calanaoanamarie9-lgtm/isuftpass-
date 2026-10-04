<?php

namespace App\Support;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Builds a complete, downloadable "pass card" containing everything shown on
 * screen — branding header, student identity, the transaction QR, reference
 * number and the verified footer — not just the bare QR code.
 *
 * Two renderers, deliberately:
 *
 *   svg()  vector, prints and scales without limit, but a phone cannot open
 *          an .svg — Android reports "Couldn't open file" because no gallery
 *          or viewer claims image/svg+xml.
 *   png()  raster, opens in every phone's photo viewer. This is what the
 *          download buttons serve.
 *
 * Both draw from one layout definition so the two can never drift apart.
 * The card is drawn with GD because the environment has no SVG rasteriser
 * (imagick is absent and rsvg is not installed), and text is rendered with
 * Open Sans, which endroid/qr-code already ships in vendor/ — no font is
 * added to the repository for this.
 */
class QrPassCard
{
    /** Card geometry, shared by both renderers. */
    private const W = 640;
    private const H = 1010;
    private const RADIUS = 28;
    private const HEADER_H = 170;
    private const FOOTER_Y = 976;
    private const QR = [155, 556, 330];

    /** The same hexes the SVG uses, as GD palette entries. */
    private const C = [
        'white' => '#ffffff',
        'navy' => '#102d5b',
        'paleBlue' => '#9db8e8',
        'slate' => '#94a3b8',
        'ink' => '#0f172a',
        'rule' => '#e2e8f0',
        'blue' => '#1d4ed8',
        'caption' => '#64748b',
        'green' => '#059669',
        'edge' => '#cbd5e1',
    ];

    public static function svg(array $options): string
    {
        [
            'sectionLabel' => $sectionLabel,
            'refCode' => $refCode,
            'caption' => $caption,
            'name' => $name,
            'studentId' => $studentId,
            'course' => $course,
            'yearLevel' => $yearLevel,
            'qrPayload' => $qrPayload,
        ] = array_merge(self::defaults(), $options);

        $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

        // Downscaled logo keeps the SVG light while keeping official branding.
        $logo = self::logoDataUri();

        $identity = [
            ['Name', $name],
            ['Student ID', $studentId],
            ['Course / Program', $course],
            ['Year Level', $yearLevel],
        ];

        $svg = [];
        $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="640" height="1010" viewBox="0 0 640 1010">';
        $svg[] = '<defs><clipPath id="card"><rect width="640" height="1010" rx="28"/></clipPath></defs>';
        $svg[] = '<g clip-path="url(#card)">';
        $svg[] = '<rect width="640" height="1010" fill="#ffffff"/>';

        // Header band
        $svg[] = '<rect width="640" height="170" fill="#102d5b"/>';
        if ($logo) {
            $svg[] = '<image x="34" y="45" width="80" height="80" xlink:href="' . $logo . '" href="' . $logo . '"/>';
        }
        $tx = $logo ? 134 : 44;
        $svg[] = '<text x="' . $tx . '" y="92" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="800" fill="#ffffff">ISUFSTPASS</text>';
        $svg[] = '<text x="' . $tx . '" y="122" font-family="Arial, Helvetica, sans-serif" font-size="15" letter-spacing="3" fill="#9db8e8">DIGITAL STUDENT ID</text>';

        // Identity block
        $y = 216;
        foreach ($identity as [$label, $value]) {
            $size = mb_strlen((string) $value) > 30 ? 19 : 24;
            $svg[] = '<text x="44" y="' . $y . '" font-family="Arial, Helvetica, sans-serif" font-size="13" letter-spacing="2" fill="#94a3b8">' . $e(mb_strtoupper($label)) . '</text>';
            $svg[] = '<text x="44" y="' . ($y + 30) . '" font-family="Arial, Helvetica, sans-serif" font-size="' . $size . '" font-weight="700" fill="#0f172a">' . $e($value !== '' ? $value : '&#8212;') . '</text>';
            $y += 64;
        }

        $svg[] = '<line x1="44" y1="486" x2="596" y2="486" stroke="#e2e8f0" stroke-width="2"/>';

        // Transaction section
        $svg[] = '<text x="320" y="532" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="22" font-weight="800" letter-spacing="1" fill="#1d4ed8">' . $e(mb_strtoupper($sectionLabel)) . '</text>';

        $qrUri = (new PngWriter())->write(new QrCode($qrPayload))->getDataUri();
        $svg[] = '<image x="155" y="556" width="330" height="330" xlink:href="' . $qrUri . '" href="' . $qrUri . '"/>';

        $svg[] = '<text x="320" y="926" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="21" font-weight="800" fill="#0f172a">' . $e($refCode) . '</text>';

        if ($caption !== '') {
            $svg[] = '<text x="320" y="954" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="14" fill="#64748b">' . $e($caption) . '</text>';
        }

        // Verified footer
        $svg[] = '<rect y="976" width="640" height="34" fill="#059669"/>';
        $svg[] = '<text x="320" y="998" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="800" letter-spacing="2" fill="#ffffff">ISUFSTPASS VERIFIED &#8226; SECURE &#8226; RELIABLE &#8226; OFFICIAL</text>';

        $svg[] = '</g>';
        $svg[] = '<rect x="1.5" y="1.5" width="637" height="1007" rx="26.5" fill="none" stroke="#cbd5e1" stroke-width="3"/>';
        $svg[] = '</svg>';

        return implode("\n", $svg);
    }

    /**
     * The same card as a PNG — what a phone can actually open.
     *
     * Returns raw PNG bytes.
     */
    public static function png(array $options): string
    {
        [
            'sectionLabel' => $sectionLabel,
            'refCode' => $refCode,
            'caption' => $caption,
            'name' => $name,
            'studentId' => $studentId,
            'course' => $course,
            'yearLevel' => $yearLevel,
            'qrPayload' => $qrPayload,
        ] = array_merge(self::defaults(), $options);

        $im = imagecreatetruecolor(self::W, self::H);
        $ink = [];

        foreach (self::C as $key => $hex) {
            $ink[$key] = self::color($im, $hex);
        }

        imagefilledrectangle($im, 0, 0, self::W - 1, self::H - 1, $ink['white']);

        // --- Header band ---------------------------------------------------
        imagefilledrectangle($im, 0, 0, self::W - 1, self::HEADER_H - 1, $ink['navy']);

        $logo = self::logoImage();
        $tx = 44;

        if ($logo) {
            imagecopyresampled($im, $logo, 34, 45, 0, 0, 80, 80, imagesx($logo), imagesy($logo));
            imagedestroy($logo);
            $tx = 134;
        }

        $font = self::fontPath();
        self::text($im, $font, 34, $tx, 92, $ink['white'], 'ISUFSTPASS', ['bold' => true]);
        self::text($im, $font, 15, $tx, 122, $ink['paleBlue'], 'DIGITAL STUDENT ID', ['track' => 3]);

        // --- Identity block ------------------------------------------------
        $identity = [
            ['Name', $name],
            ['Student ID', $studentId],
            ['Course / Program', $course],
            ['Year Level', $yearLevel],
        ];

        $y = 216;

        foreach ($identity as [$label, $value]) {
            $size = mb_strlen((string) $value) > 30 ? 19 : 24;

            self::text($im, $font, 13, 44, $y, $ink['slate'], mb_strtoupper($label), ['track' => 2]);
            self::text($im, $font, $size, 44, $y + 30, $ink['ink'], $value !== '' ? (string) $value : '—', ['bold' => true]);

            $y += 64;
        }

        imagefilledrectangle($im, 44, 486, 596, 487, $ink['rule']);

        // --- Transaction section -------------------------------------------
        self::text(
            $im, $font, 22, (int) (self::W / 2), 532, $ink['blue'],
            mb_strtoupper($sectionLabel),
            ['bold' => true, 'track' => 1, 'anchor' => 'middle']
        );

        self::pasteQr($im, $qrPayload);

        self::text($im, $font, 21, (int) (self::W / 2), 926, $ink['ink'], (string) $refCode, ['bold' => true, 'anchor' => 'middle']);

        if ($caption !== '') {
            self::text($im, $font, 14, (int) (self::W / 2), 954, $ink['caption'], (string) $caption, ['anchor' => 'middle']);
        }

        // --- Verified footer ------------------------------------------------
        imagefilledrectangle($im, 0, self::FOOTER_Y, self::W - 1, self::H - 1, $ink['green']);

        self::text(
            $im, $font, 16, (int) (self::W / 2), 998, $ink['white'],
            'ISUFSTPASS VERIFIED • SECURE • RELIABLE • OFFICIAL',
            ['bold' => true, 'track' => 2, 'anchor' => 'middle']
        );

        // --- Rounded corners, then the 3px edge -----------------------------
        self::roundCorners($im, self::W, self::H, self::RADIUS, $ink['white']);
        self::roundedBorder($im, self::W, self::H, self::RADIUS, $ink['edge']);

        ob_start();
        imagepng($im, null, 6);
        $bytes = (string) ob_get_clean();

        imagedestroy($im);

        return $bytes;
    }

    private static function defaults(): array
    {
        return [
            'sectionLabel' => 'Transaction QR',
            'refCode' => '',
            'caption' => '',
            'name' => '',
            'studentId' => '',
            'course' => '',
            'yearLevel' => '',
            'qrPayload' => '',
        ];
    }

    /**
     * Decode the reference glyph to raw PNG, then seat it in the card.
     *
     * Copied 1:1 and centred, never rescaled. endroid emits this QR at 8px
     * per module over 37 modules with a 12px quiet zone; stretching it to
     * the 330px slot would land modules on 8.25px, and a reader samples
     * whole modules — the resample is what turns a crisp code into a fuzzy
     * one a phone camera has to hunt for.
     */
    private static function pasteQr(\GdImage $im, string $payload): void
    {
        [$x, $y, $box] = self::QR;

        $raw = (string) (new PngWriter())->write(new QrCode($payload))->getString();
        $qr = @imagecreatefromstring($raw);

        if ($qr === false) {
            return;
        }

        $dx = $x + (int) floor(($box - imagesx($qr)) / 2);
        $dy = $y + (int) floor(($box - imagesy($qr)) / 2);

        imagecopy($im, $qr, $dx, $dy, 0, 0, imagesx($qr), imagesy($qr));
        imagedestroy($qr);
    }

    /**
     * Paint the outside of each rounded corner with the card background so
     * the corners read as rounded rather than square.
     */
    private static function roundCorners(\GdImage $im, int $w, int $h, int $r, int $bg): void
    {
        $centres = [
            [$r, $r, 0, 0],
            [$w - 1 - $r, $r, $w - $r, 0],
            [$r, $h - 1 - $r, 0, $h - $r],
            [$w - 1 - $r, $h - 1 - $r, $w - $r, $h - $r],
        ];

        foreach ($centres as [$cx, $cy, $x0, $y0]) {
            for ($y = $y0; $y < $y0 + $r + 1 && $y < $h; $y++) {
                for ($x = $x0; $x < $x0 + $r + 1 && $x < $w; $x++) {
                    $dx = $x - $cx;
                    $dy = $y - $cy;

                    if ($dx * $dx + $dy * $dy > $r * $r) {
                        imagesetpixel($im, $x, $y, $bg);
                    }
                }
            }
        }
    }

    /**
     * The SVG draws a 3px rounded stroke. GD has no line width for arcs, so
     * three concentric outlines at inset 1/2/3 land on the same visual weight.
     */
    private static function roundedBorder(\GdImage $im, int $w, int $h, int $r, int $color): void
    {
        foreach ([1, 2, 3] as $inset) {
            $rr = $r - $inset;
            $l = $inset;
            $right = $w - 1 - $inset;
            $bottom = $h - 1 - $inset;

            imageline($im, $l + $rr, $l, $right - $rr, $l, $color);
            imageline($im, $l + $rr, $bottom, $right - $rr, $bottom, $color);
            imageline($im, $l, $l + $rr, $l, $bottom - $rr, $color);
            imageline($im, $right, $l + $rr, $right, $bottom - $rr, $color);

            imagearc($im, $l + $rr, $l + $rr, $rr * 2, $rr * 2, 180, 270, $color);
            imagearc($im, $right - $rr, $l + $rr, $rr * 2, $rr * 2, 270, 360, $color);
            imagearc($im, $right - $rr, $bottom - $rr, $rr * 2, $rr * 2, 0, 90, $color);
            imagearc($im, $l + $rr, $bottom - $rr, $rr * 2, $rr * 2, 90, 180, $color);
        }
    }

    /**
     * Draw one run of text. $anchor shifts the x origin so callers can pass
     * a centre point; $track reproduces the SVG's letter-spacing, which GD
     * has no equivalent for; $bold is faked by overprinting one pixel right,
     * because the shipped face is a single weight.
     */
    private static function text(
        \GdImage $im,
        ?string $font,
        float $size,
        int $x,
        int $y,
        int $color,
        string $value,
        array $opts = []
    ): void {
        if ($value === '') {
            return;
        }

        $bold = $opts['bold'] ?? false;
        $track = (int) ($opts['track'] ?? 0);
        $anchor = $opts['anchor'] ?? 'left';

        // No TTF anywhere: fall back to GD's built-in face. Smaller and
        // plainer, but the card still carries every piece of information.
        if ($font === null) {
            $level = $size >= 24 ? 5 : ($size >= 15 ? 4 : 3);
            $sx = $anchor === 'middle' ? $x - (int) (mb_strlen($value) * $size * 0.3) : $x;
            imagestring($im, $level, $sx, max(0, $y - (int) $size), $value, $color);

            return;
        }

        $chars = $track > 0 ? mb_str_split($value) : [$value];
        $width = 0;

        foreach ($chars as $char) {
            $width += self::charWidth($font, $size, $char) + ($track > 0 ? $track : 0);
        }

        if ($track > 0 && $chars !== []) {
            $width -= $track;
        }

        if ($anchor === 'middle') {
            $x -= (int) round($width / 2);
        } elseif ($anchor === 'right') {
            $x -= (int) round($width);
        }

        foreach ($chars as $char) {
            imagettftext($im, $size, 0, $x, $y, $color, $font, $char);

            if ($bold) {
                imagettftext($im, $size, 0, $x + 1, $y, $color, $font, $char);
            }

            $x += self::charWidth($font, $size, $char) + $track;
        }
    }

    private static function charWidth(string $font, float $size, string $char): int
    {
        // A space reports near-zero ink; without this, tracked words collide.
        if ($char === ' ') {
            return (int) round($size * 0.30);
        }

        $box = @imagettfbbox($size, 0, $font, $char);

        if ($box === false) {
            return (int) ceil($size * 0.6);
        }

        return max(1, (int) round(abs($box[2] - $box[0])));
    }

    /**
     * Open Sans, already present as a dependency of endroid/qr-code, with
     * common system faces behind it in case that package ever drops the asset.
     */
    private static function fontPath(): ?string
    {
        foreach ([
            base_path('vendor/endroid/qr-code/assets/open_sans.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
        ] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function color(\GdImage $im, string $hex): int
    {
        return imagecolorallocate(
            $im,
            (int) hexdec(substr($hex, 1, 2)),
            (int) hexdec(substr($hex, 3, 2)),
            (int) hexdec(substr($hex, 5, 2))
        );
    }

    /**
     * The brand asset is JPEG bytes behind a .png name, so imagecreatefrompng
     * rejects it. imagecreatefromstring sniffs the real format.
     */
    private static function logoImage(): ?\GdImage
    {
        $path = public_path('img/isufstpass-logo.png');

        if (! is_file($path)) {
            return null;
        }

        $image = @imagecreatefromstring((string) file_get_contents($path));

        return $image === false ? null : $image;
    }

    /**
     * Downscale the brand logo so the downloaded SVG stays lightweight.
     */
    private static function logoDataUri(): ?string
    {
        $src = self::logoImage();

        if ($src === null) {
            $path = public_path('img/isufstpass-logo.png');

            // Keep the SVG branded even if the bytes cannot be decoded.
            if (! is_file($path)) {
                return null;
            }

            $raw = (string) file_get_contents($path);

            return $raw !== '' ? 'data:image/png;base64,' . base64_encode($raw) : null;
        }

        $size = 160;
        $dst = imagecreatetruecolor($size, $size);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);

        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);

        imagecopyresampled(
            $dst,
            $src,
            0,
            0,
            (int) (($w - $side) / 2),
            (int) (($h - $side) / 2),
            $size,
            $size,
            $side,
            $side
        );

        ob_start();
        imagepng($dst, null, 6);
        $data = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $data ? 'data:image/png;base64,' . base64_encode($data) : null;
    }
}

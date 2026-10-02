<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Derive every favicon/PWA asset from the one approved brand asset so the icon
 * set can never drift from the seal that ships on the site. Run it after
 * replacing public/img/isufstpass-logo.png.
 *
 *     php artisan icons:generate
 *
 * Notes on the source asset:
 *  - 1254x1254 square: a circular seal on a #FCFCFC field with ~2% margin.
 *  - Despite the .png extension the bytes are JPEG, so there is no alpha
 *    channel. Every output is therefore flattened onto that same #FCFCFC
 *    field rather than composited over transparency, which would render the
 *    corners black when iOS installs the touch icon.
 */
class GenerateIcons extends Command
{
    /**
     * The sampled background of the seal. Used for the icon field and as the
     * manifest's background_color so a cold app launch paints the same tone.
     */
    private const FIELD = [0xFC, 0xFC, 0xFC];

    /**
     * Sizes the .ico carries. 16/32/48 covers browser tabs, bookmarks and
     * Windows' taskbar pinning; entries are PNG-encoded, which every browser
     * and Windows since Vista accepts and which keeps this file small.
     */
    private const FAVICON_SIZES = [16, 32, 48];

    /**
     * name => [canvas size, seal ratio of the canvas].
     *
     * A ratio of 1.0 is full bleed. The maskable icon must keep all content
     * inside the inner 80% safe zone because Android crops a circle out of it,
     * and the touch icon keeps a margin because iOS squircles the corners.
     */
    private const ICONS = [
        'icon-192.png'          => [192, 1.0],
        'icon-512.png'          => [512, 1.0],
        'icon-maskable-512.png' => [512, 0.80],
        'apple-touch-icon.png'  => [180, 0.92],
    ];

    protected $signature = 'icons:generate';

    protected $description = 'Derive favicon.ico and the PWA icon set from public/img/isufstpass-logo.png';

    public function handle(): int
    {
        $source = public_path('img/isufstpass-logo.png');

        if (! File::exists($source)) {
            $this->error("Source logo not found: {$source}");

            return self::FAILURE;
        }

        $logo = @imagecreatefromstring(File::get($source));

        if ($logo === false) {
            $this->error("GD could not decode {$source}. Check that the gd extension is enabled.");

            return self::FAILURE;
        }

        $directory = public_path('img/icons');
        File::ensureDirectoryExists($directory);

        // favicon.ico
        $entries = [];
        foreach (self::FAVICON_SIZES as $size) {
            $entries[] = [$size, $this->png($this->compose($logo, $size, 1.0))];
        }
        File::put(public_path('favicon.ico'), $this->ico($entries));
        $this->line(sprintf('  favicon.ico  %6d bytes  (%s)', filesize(public_path('favicon.ico')), implode('/', self::FAVICON_SIZES)));

        // PNG icon set
        foreach (self::ICONS as $name => [$size, $ratio]) {
            $path = $directory.'/'.$name;
            File::put($path, $this->png($this->compose($logo, $size, $ratio)));
            $this->line(sprintf('  %-24s %6d bytes  (%dpx, seal %.0f%%)', $name, filesize($path), $size, $ratio * 100));
        }

        imagedestroy($logo);

        $this->newLine();
        $this->info('Icons written to public/img/icons and public/favicon.ico.');

        return self::SUCCESS;
    }

    /**
     * A square canvas of $size filled with the brand field, with the seal
     * centred at $ratio of the canvas.
     *
     * @param  \GdImage  $logo
     * @return \GdImage
     */
    private function compose($logo, int $size, float $ratio)
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, false);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, ...self::FIELD));

        $seal = (int) round($size * $ratio);
        $offset = (int) floor(($size - $seal) / 2);
        $scaled = $this->resize($logo, $seal);

        imagecopyresampled($canvas, $scaled, $offset, $offset, 0, 0, $seal, $seal, imagesx($scaled), imagesy($scaled));

        if ($scaled !== $logo) {
            imagedestroy($scaled);
        }

        return $canvas;
    }

    /**
     * Scale by halving until we are within 2x of the target, then resample
     * once more. A single 1254->16 jump aliases the seal's rim into mush;
     * repeated halving keeps it readable at favicon sizes.
     *
     * @param  \GdImage  $source
     * @return \GdImage
     */
    private function resize($source, int $target)
    {
        $current = $source;
        $owned = false;

        while (imagesx($current) > $target * 2 && imagesy($current) > $target * 2) {
            $next = imagecreatetruecolor(
                max(1, (int) floor(imagesx($current) / 2)),
                max(1, (int) floor(imagesy($current) / 2))
            );
            imagecopyresampled($next, $current, 0, 0, 0, 0, imagesx($next), imagesy($next), imagesx($current), imagesy($current));

            if ($owned) {
                imagedestroy($current);
            }

            $current = $next;
            $owned = true;
        }

        if (imagesx($current) === $target && imagesy($current) === $target) {
            return $current;
        }

        $out = imagecreatetruecolor($target, $target);
        imagecopyresampled($out, $current, 0, 0, 0, 0, $target, $target, imagesx($current), imagesy($current));

        if ($owned) {
            imagedestroy($current);
        }

        return $out;
    }

    /**
     * @param  \GdImage  $image
     */
    private function png($image): string
    {
        ob_start();
        imagepng($image, null, 9);

        return ob_get_clean();
    }

    /**
     * Wrap PNG blobs in a standards-compliant .ico container:
     * ICONDIR, then one 16-byte ICONDIRENTRY per image, then the blobs.
     *
     * @param  array<int, array{0: int, 1: string}>  $images
     */
    private function ico(array $images): string
    {
        $binary = pack('vvv', 0, 1, count($images)); // reserved, type = icon, count
        $entries = '';
        $blobs = '';
        $offset = 6 + (16 * count($images));

        foreach ($images as [$size, $data]) {
            $dimension = $size >= 256 ? 0 : $size; // 0 encodes 256
            $entries .= pack('CCCC', $dimension, $dimension, 0, 0);       // w, h, colours, reserved
            $entries .= pack('vv', 1, 32);                                // planes, bit count
            $entries .= pack('VV', strlen($data), $offset);               // bytes, image offset
            $blobs .= $data;
            $offset += strlen($data);
        }

        return $binary.$entries.$blobs;
    }
}

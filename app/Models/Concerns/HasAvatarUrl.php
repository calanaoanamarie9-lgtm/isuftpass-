<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Builds a browser-facing URL for the photo stored in the `avatar` column.
 *
 * The column holds a path relative to the configured avatar disk, e.g.
 * "avatars/abc.jpg". Views must render `$model->avatar_url` instead of
 * `Storage::url($model->avatar)`:
 *
 * - Storage::url() reads the *default* disk (FILESYSTEM_DISK=local), which has
 *   no base URL, so it returns the domain-root path "/storage/avatars/abc.jpg".
 *   Served from a sub-directory (XAMPP runs the app at /Isufstpass) the browser
 *   requested http://localhost/storage/avatars/abc.jpg, got a 404 and fell back
 *   to the broken-image state showing the truncated "Profile Picture" alt text.
 * - The public disk's own URL is APP_URL, which is the production host even
 *   when running locally, so that would break the other way around.
 * - asset() looks correct at first glance, but AppServiceProvider calls
 *   URL::forceRootUrl(config('app.url')) to keep email links off dev hosts —
 *   that pins every generated URL (assets included) to APP_URL, which again
 *   points a local page at the production host.
 *
 * So the URL is built from the request's own base path: root-relative, no
 * scheme or host to get wrong behind proxies, correct both at the domain root
 * (production) and behind XAMPP's /Isufstpass sub-directory, and it follows
 * whatever host the browser actually used. Cloud disks (AVATAR_DISK=supabase)
 * publish their own base URL and are resolved through the disk instead.
 */
trait HasAvatarUrl
{
    /**
     * Public URL of the stored avatar, or null when none is set.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->avatar;

        if (blank($path)) {
            return null;
        }

        // Already a fully-qualified URL (seeded or externally hosted photo).
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $disk = (string) config('filesystems.avatar', 'public');

        // Cloud disks (supabase / s3) expose their own public base URL.
        if ($disk !== 'public') {
            return Storage::disk($disk)->url($path);
        }

        // The local public disk is exposed through the public/storage symlink.
        // Root-relative to the request's base path: resolves against the host
        // and sub-directory the page was really served from (XAMPP runs the
        // app at /Isufstpass, production at the domain root), which neither
        // Storage::url() (domain root only) nor asset() (pinned to APP_URL by
        // forceRootUrl) can produce here.
        return request()->getBaseUrl() . '/storage/' . ltrim($path, '/');
    }
}

<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Turns an uploaded photo into a data URI for storage in the database.
 *
 * Profile photos are kept as base64 in Postgres (avatar_data) rather than as
 * files on a disk, because Render's filesystem is ephemeral and the Supabase
 * bucket was never credentialled — both lost photos. The database is shared
 * and persistent, so a data URI here survives deploys and restarts. Views
 * render it straight into <img src>, which every browser supports.
 */
class Avatar
{
    /**
     * Build a self-contained data URI from an uploaded image.
     *
     * The upload rules already restrict the file to jpg/jpeg/png/webp, so the
     * detected mime type is trusted to label the bytes correctly.
     */
    public static function fromUploadedFile(UploadedFile $file): string
    {
        $mime = $file->getMimeType() ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($file->getRealPath()));
    }
}

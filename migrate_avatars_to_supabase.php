<?php
// One-off: push locally-stored avatar files into the Supabase Storage bucket.
//
// WHY THIS EXISTS: the database is shared between local dev and production,
// but the avatar *files* are not. Photos uploaded from this machine only
// exist in storage/app/public/avatars/, so the live site 404s on them. After
// render.yaml sets AVATAR_DISK=supabase, new uploads persist — but the few
// photos below were never in the bucket. This copies them across.
//
// TO RUN: put the Supabase S3 access keys in .env first (they are the same
// values set in the Render dashboard), then:
//   php migrate_avatars_to_supabase.php
//
// It is idempotent: a file already present in the bucket is skipped.

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

$srcDisk = 'public'; // where the local files live
$dstDisk = config('filesystems.avatar');

echo "Source disk : {$srcDisk}".PHP_EOL;
echo "Destination : {$dstDisk}".PHP_EOL;

if ($dstDisk !== 'supabase') {
    echo "ABORT: AVATAR_DISK is not 'supabase'. Set it in .env and retry.".PHP_EOL;
    exit(1);
}

$keys = [config('filesystems.disks.supabase.key'), config('filesystems.disks.supabase.secret')];
if (in_array(null, $keys, true) || in_array('', $keys, true)) {
    echo "ABORT: S3_ACCESS_KEY_ID / S3_SECRET_ACCESS_KEY missing from .env.".PHP_EOL;
    exit(1);
}

$src = Storage::disk($srcDisk);
$dst = Storage::disk($dstDisk);

$paths = collect()
    ->merge(User::whereNotNull('avatar')->pluck('avatar'))
    ->merge(StudentProfile::whereNotNull('avatar')->pluck('avatar'))
    ->unique()
    ->values();

$copied = $skipped = $missing = 0;

foreach ($paths as $path) {
    if (! $src->exists($path)) {
        echo "  [missing locally] {$path}".PHP_EOL;
        $missing++;
        continue;
    }

    if ($dst->exists($path)) {
        echo "  [already in bucket] {$path}".PHP_EOL;
        $skipped++;
        continue;
    }

    $dst->put($path, $src->get($path), 'public');
    echo "  [copied] {$path}".PHP_EOL;
    $copied++;
}

echo PHP_EOL."Done. copied={$copied} skipped={$skipped} missingLocally={$missing}".PHP_EOL;

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "STATUS COUNTS:\n";
foreach (DB::table('document_requests')->selectRaw('status, count(*) c, count(paid_at) paid')->groupBy('status')->get() as $r) {
    echo sprintf("  %-18s count=%d  with_paid_at=%d\n", $r->status, $r->c, $r->paid);
}

echo "\nRECENT ROWS:\n";
foreach (DB::table('document_requests')->orderByDesc('id')->limit(8)->get(['id','request_number','status','paid_at','or_number']) as $r) {
    echo sprintf("  #%d %-20s status=%-14s paid_at=%s or=%s\n",
        $r->id, $r->request_number, $r->status,
        $r->paid_at ?: '-', $r->or_number ?: '-');
}

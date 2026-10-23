<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ewtr;
use Illuminate\Support\Facades\Storage;

$ewtrs = Ewtr::all();
echo "Total EWTRs: " . $ewtrs->count() . "\n";

foreach ($ewtrs as $ewtr) {
    echo "ID: {$ewtr->id} | No: {$ewtr->nomor_ewtr} | Keterangan: '{$ewtr->keterangan}' | File: {$ewtr->pdf_path}\n";
    $stampedExists = $ewtr->pdf_path && Storage::disk('public')->exists($ewtr->pdf_path);
    $rawPath = $ewtr->pdf_path ? str_replace('.pdf', '_raw.pdf', $ewtr->pdf_path) : null;
    $rawExists = $rawPath && Storage::disk('public')->exists($rawPath);
    echo "  Stamped exists: " . ($stampedExists ? 'YES' : 'NO') . " | Raw exists: " . ($rawExists ? 'YES' : 'NO') . "\n";
}

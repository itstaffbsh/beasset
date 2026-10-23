<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ewtr;
use Illuminate\Support\Facades\Storage;

$ewtrs = Ewtr::all();
foreach ($ewtrs as $ewtr) {
    if (!$ewtr->keterangan) continue;
    $fullPath = Storage::disk('public')->path($ewtr->pdf_path);
    $cmd = 'python -c "import pypdf; r = pypdf.PdfReader(r\''. $fullPath .'\'); print(r\''. $ewtr->keterangan .'\' in r.pages[0].extract_text())"';
    $res = trim(shell_exec($cmd));
    echo "EWTR {$ewtr->nomor_ewtr}: contains '{$ewtr->keterangan}'? $res\n";
}

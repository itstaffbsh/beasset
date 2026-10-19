<?php

// Memuat framework Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Artisan;

try {
    echo "Sedang menjalankan migrasi...<br>";
    Artisan::call('migrate', ['--force' => true]);
    echo "<b>BERHASIL!</b> Database sudah diperbarui.<br>";
    echo "Silakan coba import data kembali.";
} catch (\Exception $e) {
    echo "Gagal menjalankan migrasi: " . $e->getMessage();
}

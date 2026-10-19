<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    if (Schema::hasColumn('products', 'nama_asset') && !Schema::hasColumn('products', 'description')) {
        echo "Mengubah nama kolom nama_asset menjadi description...<br>";
        DB::statement("ALTER TABLE products CHANGE nama_asset description TEXT NULL");
        echo "<b style='color:green'>BERHASIL!</b> Kolom sudah di-rename.<br>";
    } else {
        echo "Kolom sudah sesuai atau kolom 'nama_asset' tidak ditemukan.<br>";
    }
    
    echo "<br><a href='products'>Kembali ke Aplikasi</a>";
} catch (\Exception $e) {
    echo "Gagal rename secara paksa: " . $e->getMessage();
}

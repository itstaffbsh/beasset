<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('products');

echo "<h3>Daftar Kolom di Tabel 'products':</h3>";
echo "<ul>";
foreach ($columns as $column) {
    echo "<li>$column</li>";
}
echo "</ul>";

if (in_array('description', $columns)) {
    echo "<b style='color:green'>Kolom 'description' DITEMUKAN.</b>";
} else {
    echo "<b style='color:red'>Kolom 'description' TIDAK ADA.</b>";
}

if (in_array('nama_asset', $columns)) {
    echo "<br><b style='color:orange'>Kolom 'nama_asset' masih ada.</b>";
}

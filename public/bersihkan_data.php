<?php

use Illuminate\Support\Facades\DB;
use App\Models\ProductHistory;
use App\Models\Aset;
use App\Models\User;

// Karena ini file public, kita perlu memuat framework Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Mulai Pembersihan Total
try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // 1. Hapus Semua Data Transaksi dan Aset
    DB::table('product_histories')->truncate();
    DB::table('products')->truncate();
    echo "✔ Semua data Aset dan Riwayat berhasil dihapus.<br>";
    
    // 2. Hapus Semua Master Data (Struktur)
    DB::table('departments')->truncate();
    DB::table('offices')->truncate();
    DB::table('classifications')->truncate();
    echo "✔ Semua data Departemen, Kantor, dan Klasifikasi berhasil dihapus.<br>";
    
    // 3. Hapus User Non-Admin (Serta bersihkan link departemen/kantor di user admin agar tidak error)
    User::where('role', 'super_admin')->orWhere('role', 'admin')->update([
        'department_id' => null,
        'office_id' => null
    ]);
    
    $deletedUsers = User::whereNotIn('role', ['super_admin', 'admin'])->delete();
    echo "✔ Semua User Non-Admin berhasil dihapus ($deletedUsers user).<br>";
    echo "✔ User Admin & Super Admin tetap dipertahankan (Link departemen/kantor direset ke NULL).<br>";
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    echo "<br><b>RESET TOTAL SELESAI!</b><br>Silakan login kembali dan mulai mengisi data dari awal.";
} catch (\Exception $e) {
    echo "Gagal melakukan reset: " . $e->getMessage();
}

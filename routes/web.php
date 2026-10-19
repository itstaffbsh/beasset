<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

/* | [PROSEDUR] | Halaman Selamat Datang saat pertama kali dibuka */
Route::get('/', function () {
    return redirect()->route('login');
});

/* | [PROSEDUR DARURAT] | 
   | Jalankan URL '/perbaiki-database' jika terjadi error "Column description not found" 
   | Kegunaan: Mengubah nama kolom 'nama_asset' menjadi 'description' secara otomatis.
*/
Route::get('/perbaiki-database', function() {
    try {
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'nama_asset')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE products CHANGE nama_asset description TEXT NULL");
            return "BERHASIL! Kolom nama_asset sudah diubah menjadi description. Silakan coba import lagi.";
        }
        return "Database sudah dalam kondisi benar atau kolom nama_asset tidak ditemukan.";
    } catch (\Exception $e) {
        return "Gagal perbaiki: " . $e->getMessage();
    }
});

/* | [PROSEDUR DARURAT] | 
   | Jalankan URL '/tambah-kolom-karyawan' untuk menambahkan kolom detail karyawan.
   | Kegunaan: Menambahkan kolom job_position, job_level, join_date, phone_number ke tabel users.
*/

/* | [PROSEDUR DARURAT] | 
   | Jalankan URL '/tambah-kolom-sttb' untuk menambahkan kolom STTB.
   | Kegunaan: Menambahkan batch_id, quantity, pihak_pertama_id, tanggal_pinjam ke product_histories.
*/
Route::get('/tambah-kolom-sttb', function() {
    $results = [];
    $columns = [
        'batch_id'          => "ALTER TABLE `product_histories` ADD COLUMN `batch_id` VARCHAR(36) NULL AFTER `id`",
        'quantity'          => "ALTER TABLE `product_histories` ADD COLUMN `quantity` INT NOT NULL DEFAULT 1 AFTER `jenis_transaksi`",
        'pihak_pertama_id'  => "ALTER TABLE `product_histories` ADD COLUMN `pihak_pertama_id` BIGINT UNSIGNED NULL AFTER `quantity`",
        'tanggal_pinjam'    => "ALTER TABLE `product_histories` ADD COLUMN `tanggal_pinjam` DATE NULL AFTER `pihak_pertama_id`",
    ];
    foreach ($columns as $col => $sql) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('product_histories', $col)) {
            try {
                \Illuminate\Support\Facades\DB::statement($sql);
                $results[] = "✅ Kolom '$col' berhasil ditambahkan.";
            } catch (\Exception $e) {
                $results[] = "❌ Gagal '$col': " . $e->getMessage();
            }
        } else {
            $results[] = "ℹ️ Kolom '$col' sudah ada.";
        }
    }
    return implode("<br>", $results) . "<br><br><b>Selesai! Fitur STTB siap digunakan.</b>";
});
Route::get('/tambah-kolom-karyawan', function() {
    $db = \Illuminate\Support\Facades\DB::connection()->getPdo();
    $results = [];

    $columns = [
        'job_position' => "ALTER TABLE `users` ADD COLUMN `job_position` VARCHAR(255) NULL AFTER `role`",
        'job_level'    => "ALTER TABLE `users` ADD COLUMN `job_level` VARCHAR(255) NULL AFTER `job_position`",
        'join_date'    => "ALTER TABLE `users` ADD COLUMN `join_date` DATE NULL AFTER `job_level`",
        'phone_number' => "ALTER TABLE `users` ADD COLUMN `phone_number` VARCHAR(20) NULL AFTER `join_date`",
    ];

    foreach ($columns as $col => $sql) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', $col)) {
            try {
                \Illuminate\Support\Facades\DB::statement($sql);
                $results[] = "✅ Kolom '$col' berhasil ditambahkan.";
            } catch (\Exception $e) {
                $results[] = "❌ Gagal menambahkan '$col': " . $e->getMessage();
            }
        } else {
            $results[] = "ℹ️ Kolom '$col' sudah ada, dilewati.";
        }
    }

    return implode("<br>", $results) . "<br><br><b>Selesai! Silakan coba import karyawan lagi.</b>";
});

Route::get('/tambah-status-user', function() {
    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `users` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `role`");
        return "✅ Kolom 'status' berhasil ditambahkan ke tabel users.";
    }
    return "ℹ️ Kolom 'status' sudah ada.";
});

/* | [PROSEDUR] | Halaman Dashboard utama setelah Admin login */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/* | [GRUP ROUTE] | Rute yang memerlukan login (Authentication) */
Route::middleware('auth')->group(function () {
    /* | [PROSEDUR] | Mengelola profil pribadi admin yang sedang login */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* | [GRUP ROUTE] | KHUSUS SUPER ADMIN (Fitur Berisiko Tinggi) */
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    /* | [PROSEDUR] | Mengatur hak akses user dan penghapusan user */
    Route::post('/users/{user}/make-admin', [UserController::class, 'makeAdmin'])->name('users.make-admin');
    Route::get('/users/{user}/details', [UserController::class, 'details'])->name('users.details');
    Route::post('/users/{user}/resign', [UserController::class, 'resign'])->name('users.resign');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    
    /* | [PROSEDUR] | Manajemen Sampah (Mengembalikan data yang sudah dihapus) */
    Route::get('/products-trash', [AsetController::class, 'trashIndex'])->name('products.trash');
    Route::post('/products-trash/{id}/restore', [AsetController::class, 'restore'])->name('products.restore');
    Route::delete('/products-trash/{id}/force', [AsetController::class, 'forceDelete'])->name('products.forceDelete');
});

/* | [GRUP ROUTE] | UNTUK ADMIN & SUPER ADMIN (Fitur Operasional) */
Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    // [RESOURCE] | Mengelola daftar karyawan (User)
    Route::resource('users', UserController::class)->except(['destroy', 'show']);
    Route::get('/users-export', [UserController::class, 'export'])->name('users.export');
    Route::get('/users-export-pdf', [UserController::class, 'exportPdf'])->name('users.export-pdf');
    Route::post('/users-import', [UserController::class, 'import'])->name('users.import');

    // [RESOURCE] | Mengelola data Master (Kantor, Klasifikasi, Departemen)
    Route::resource('offices', \App\Http\Controllers\OfficeController::class);
    Route::resource('classifications', \App\Http\Controllers\ClassificationController::class);
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class);

    // [RESOURCE] | Manajemen Utama Aset (Tambah, Edit, Hapus Aset)
    Route::resource('products', AsetController::class);
    
    // [FUNGSI] | Fitur Ekspor/Impor data aset via Excel & PDF
    Route::get('/products-export', [AsetController::class, 'export'])->name('products.export');
    Route::get('/products-export-pdf', [AsetController::class, 'exportPdf'])->name('products.export-pdf');
    Route::post('/products-import', [AsetController::class, 'import'])->name('products.import');
    
    // [PROSEDUR] | Menampilkan halaman cetak QR Code (Dual QR: Spek & Pemilik)
    Route::get('/products/{product}/qr', [AsetController::class, 'printQr'])->name('products.qr');

    // [API] | Mendapatkan nomor urut berikutnya untuk aset baru berdasarkan departemen
    Route::get('/products/next-nomor/{department_id}', [AsetController::class, 'getNextNomor'])->name('products.nextNomor');
    
    // [PROSEDUR] | Melihat dan mengelola riwayat mutasi barang secara keseluruhan
    Route::get('/history', [AsetController::class, 'historyIndex'])->name('history.index');
    Route::get('/history/{productHistory}/edit', [AsetController::class, 'editHistory'])->name('history.edit');
    Route::put('/history/{productHistory}', [AsetController::class, 'updateHistory'])->name('history.update');
    Route::post('/history/{productHistory}/quick-return', [AsetController::class, 'quickReturn'])->name('history.quickReturn');
    Route::delete('/history/{productHistory}/cancel', [AsetController::class, 'cancelHistory'])->name('history.cancel')->middleware('role:super_admin');

    // [PROSEDUR] | Melakukan proses transaksi Pinjam dan Kembali aset
    Route::get('/transfer/pinjam', [AsetController::class, 'pinjam'])->name('products.meminjam');
    Route::get('/transfer/kembali', [AsetController::class, 'kembali'])->name('products.kembali');
    Route::post('/transfer/store', [AsetController::class, 'storeTransfer'])->name('products.storeTransfer');

    // [PROSEDUR] | Menampilkan dan mencetak STTB (Surat Tanda Terima Barang)
    Route::get('/sttb/{batch_id}', [AsetController::class, 'showSttb'])->name('sttb.show');
});

/* | [GRUP ROUTE] | AKSES SCAN QR (Akses Publik via HP) */
Route::middleware('auth')->group(function () {
    // [PROSEDUR] | Menampilkan info lengkap aset
    Route::get('/scan/{token}', [ScanController::class, 'show'])->name('scan.show');
    // [PROSEDUR] | Menampilkan KHUSUS spesifikasi teknis (QR Spek)
    Route::get('/scan/{token}/specs', [ScanController::class, 'showSpecs'])->name('scan.specs');
    // [PROSEDUR] | Menampilkan KHUSUS info pemegang & kantor (QR Pemilik)
    Route::get('/scan/{token}/owner', [ScanController::class, 'showOwner'])->name('scan.owner');
    // [FUNGSI] | API untuk kebutuhan data sistem lain (JSON)
    // [PROSEDUR] | Memperbarui info lokasi saat sedang di lapangan (Perlu Login)
    Route::post('/scan/{token}/update', [ScanController::class, 'update'])->name('scan.update');
});

require __DIR__.'/auth.php';

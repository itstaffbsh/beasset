<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menggunakan DB::statement karena Laravel Blueprint tidak mendukung perubahan ENUM secara native
        DB::statement("ALTER TABLE product_histories MODIFY COLUMN jenis_transaksi ENUM('meminjam', 'mengembalikan', 'kembali') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE product_histories MODIFY COLUMN jenis_transaksi ENUM('meminjam', 'mengembalikan') NOT NULL");
    }
};

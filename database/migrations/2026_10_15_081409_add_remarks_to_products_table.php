<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'remarks')) {
                $table->text('remarks')->nullable()->after('device_name');
            }
        });

        // Ubah device_class menjadi string agar bebas menerima nilai apapun dari Excel
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `products` MODIFY `device_class` VARCHAR(255) NULL");
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};

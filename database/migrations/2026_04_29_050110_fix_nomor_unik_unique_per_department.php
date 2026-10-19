<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if old global unique index exists before dropping
        $indexes = DB::select("SHOW INDEX FROM products WHERE Key_name = 'products_nomor_unik_unique'");
        if (!empty($indexes)) {
            DB::statement('ALTER TABLE products DROP INDEX products_nomor_unik_unique');
        }

        // Check if composite unique already exists before adding
        $existing = DB::select("SHOW INDEX FROM products WHERE Key_name = 'products_dept_nomor_unique'");
        if (empty($existing)) {
            Schema::table('products', function (Blueprint $table) {
                $table->unique(['department_id', 'nomor_unik'], 'products_dept_nomor_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['department_id', 'nomor_unik']);
            $table->unique('nomor_unik');
        });
    }
};

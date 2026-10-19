<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('department_id', 'products_department_id_index');
            $table->dropUnique('products_dept_nomor_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['department_id', 'nomor_unik'], 'products_dept_nomor_unique');
            $table->dropIndex('products_department_id_index');
        });
    }
};

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
        Schema::table('product_histories', function (Blueprint $table) {
            $table->uuid('batch_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->foreignId('pihak_pertama_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_pinjam')->nullable();
            $table->text('catatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_histories', function (Blueprint $table) {
            $table->dropForeign(['pihak_pertama_id']);
            $table->dropColumn(['batch_id', 'quantity', 'pihak_pertama_id', 'tanggal_pinjam', 'catatan']);
        });
    }
};

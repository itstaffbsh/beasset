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
        Schema::create('ewtrs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_ewtr')->nullable();
            $table->string('pdf_path');
            $table->string('pdf_name')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status')->default('belum_sukses'); // belum_sukses, sukses
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ewtr_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ewtr_id')->constrained('ewtrs')->cascadeOnDelete();
            $table->foreignId('ewtr_tool_category_id')->nullable()->constrained('ewtr_tool_categories')->nullOnDelete();
            $table->string('item_name');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ewtr_items');
        Schema::dropIfExists('ewtrs');
    }
};

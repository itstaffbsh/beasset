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
        Schema::create('asset_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('classification_id')->constrained()->onDelete('cascade');
            $table->text('specs')->nullable();
            $table->integer('qty')->default(1);
            
            // Per-item approval status for each stage
            $table->boolean('dept_approval')->default(true); // Default to true, manager will toggle
            $table->boolean('it_approval')->default(true);
            $table->boolean('md_approval')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_request_items');
    }
};

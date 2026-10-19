<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'offices',
            'classifications',
            'departments',
            'users',
            'products',
            'product_histories',
            'asset_requests',
            'asset_request_items',
            'asset_request_approvals',
            'ewtrs',
            'ewtr_items',
            'ewtr_tool_categories',
            'personal_access_tokens',
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'id')) {
                try {
                    DB::statement("ALTER TABLE `$t` MODIFY `id` CHAR(36) NOT NULL DEFAULT (UUID())");
                } catch (\Exception $e) {
                    // Ignore if already set or not supported
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

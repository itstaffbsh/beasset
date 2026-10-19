<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign keys on ewtr_items
        Schema::table('ewtr_items', function (Blueprint $table) {
            $table->dropForeign(['ewtr_id']);
            $table->dropForeign(['ewtr_tool_category_id']);
        });

        // 2. Add temporary UUID columns
        Schema::table('ewtr_tool_categories', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        Schema::table('ewtrs', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        Schema::table('ewtr_items', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->uuid('new_ewtr_id')->nullable()->after('ewtr_id');
            $table->uuid('new_category_id')->nullable()->after('ewtr_tool_category_id');
        });

        // 3. Populate UUIDs for existing categories
        $categories = DB::table('ewtr_tool_categories')->get();
        $catMap = [];
        foreach ($categories as $cat) {
            $newUuid = (string) Str::uuid();
            $catMap[$cat->id] = $newUuid;
            DB::table('ewtr_tool_categories')->where('id', $cat->id)->update(['uuid' => $newUuid]);
        }

        // 4. Populate UUIDs for existing ewtrs
        $ewtrs = DB::table('ewtrs')->get();
        $ewtrMap = [];
        foreach ($ewtrs as $ewtr) {
            $newUuid = (string) Str::uuid();
            $ewtrMap[$ewtr->id] = $newUuid;
            DB::table('ewtrs')->where('id', $ewtr->id)->update(['uuid' => $newUuid]);
        }

        // 5. Populate UUIDs for existing items and map foreign keys
        $items = DB::table('ewtr_items')->get();
        foreach ($items as $item) {
            $newUuid = (string) Str::uuid();
            $newEwtrId = $ewtrMap[$item->ewtr_id] ?? null;
            $newCatId = $catMap[$item->ewtr_tool_category_id] ?? null;

            DB::table('ewtr_items')->where('id', $item->id)->update([
                'uuid' => $newUuid,
                'new_ewtr_id' => $newEwtrId,
                'new_category_id' => $newCatId,
            ]);
        }

        // 6. Make ewtr_tool_categories.uuid the primary key
        DB::statement('ALTER TABLE ewtr_tool_categories MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE ewtr_tool_categories DROP PRIMARY KEY');
        DB::statement('ALTER TABLE ewtr_tool_categories DROP COLUMN id');
        DB::statement('ALTER TABLE ewtr_tool_categories CHANGE uuid id CHAR(36) NOT NULL PRIMARY KEY');

        // 7. Make ewtrs.uuid the primary key
        DB::statement('ALTER TABLE ewtrs MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE ewtrs DROP PRIMARY KEY');
        DB::statement('ALTER TABLE ewtrs DROP COLUMN id');
        DB::statement('ALTER TABLE ewtrs CHANGE uuid id CHAR(36) NOT NULL PRIMARY KEY');

        // 8. Make ewtr_items.uuid the primary key and replace foreign keys
        DB::statement('ALTER TABLE ewtr_items MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE ewtr_items DROP PRIMARY KEY');
        DB::statement('ALTER TABLE ewtr_items DROP COLUMN id');
        DB::statement('ALTER TABLE ewtr_items DROP COLUMN ewtr_id');
        DB::statement('ALTER TABLE ewtr_items DROP COLUMN ewtr_tool_category_id');
        DB::statement('ALTER TABLE ewtr_items CHANGE uuid id CHAR(36) NOT NULL PRIMARY KEY');
        DB::statement('ALTER TABLE ewtr_items CHANGE new_ewtr_id ewtr_id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE ewtr_items CHANGE new_category_id ewtr_tool_category_id CHAR(36) NULL');

        // 9. Recreate foreign key constraints
        Schema::table('ewtr_items', function (Blueprint $table) {
            $table->foreign('ewtr_id')->references('id')->on('ewtrs')->cascadeOnDelete();
            $table->foreign('ewtr_tool_category_id')->references('id')->on('ewtr_tool_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration to UUID
    }
};

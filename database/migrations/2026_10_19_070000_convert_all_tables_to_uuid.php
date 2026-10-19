<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Check if offices PK is already CHAR(36)
        $officeCol = DB::select("SHOW COLUMNS FROM `offices` WHERE Field = 'id'");
        if (!empty($officeCol) && str_starts_with(strtolower($officeCol[0]->Type), 'char(36)')) {
            // Already converted to UUID
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        // 1. Drop existing FK constraints to avoid errors when modifying columns
        $fkList = [
            'users' => ['users_department_id_foreign', 'users_office_id_foreign'],
            'products' => ['products_classification_id_foreign', 'products_department_id_foreign'],
            'product_histories' => [
                'product_histories_dioper_oleh_foreign',
                'product_histories_diterima_oleh_foreign',
                'product_histories_pihak_pertama_id_foreign',
                'product_histories_product_id_foreign'
            ],
            'asset_requests' => ['asset_requests_department_id_foreign', 'asset_requests_user_id_foreign'],
            'asset_request_items' => ['asset_request_items_asset_request_id_foreign', 'asset_request_items_classification_id_foreign'],
            'asset_request_approvals' => ['asset_request_approvals_asset_request_id_foreign', 'asset_request_approvals_user_id_foreign'],
            'ewtrs' => ['ewtrs_created_by_foreign'],
            'ewtr_items' => ['ewtr_items_completed_by_foreign'],
        ];

        foreach ($fkList as $tbl => $fks) {
            if (Schema::hasTable($tbl)) {
                foreach ($fks as $fk) {
                    try {
                        DB::statement("ALTER TABLE `$tbl` DROP FOREIGN KEY `$fk`");
                    } catch (\Exception $e) {}
                }
            }
        }

        // 2. Add temporary uuid columns to PK tables and populate
        $tablesWithIntPK = [
            'offices',
            'classifications',
            'departments',
            'users',
            'products',
            'product_histories',
            'asset_requests',
            'asset_request_items',
            'asset_request_approvals',
            'personal_access_tokens'
        ];

        foreach ($tablesWithIntPK as $tbl) {
            if (!Schema::hasColumn($tbl, 'uuid')) {
                DB::statement("ALTER TABLE `$tbl` ADD COLUMN `uuid` CHAR(36) NULL FIRST");
            }
            DB::statement("UPDATE `$tbl` SET `uuid` = UUID() WHERE `uuid` IS NULL OR `uuid` = ''");
        }

        // 3. Build ID mappings
        $officeMap = DB::table('offices')->pluck('uuid', 'id')->toArray();
        $classificationMap = DB::table('classifications')->pluck('uuid', 'id')->toArray();
        $departmentMap = DB::table('departments')->pluck('uuid', 'id')->toArray();
        $userMap = DB::table('users')->pluck('uuid', 'id')->toArray();
        $productMap = DB::table('products')->pluck('uuid', 'id')->toArray();
        $assetRequestMap = DB::table('asset_requests')->pluck('uuid', 'id')->toArray();

        // 4. Update foreign keys in users
        if (!Schema::hasColumn('users', 'new_office_id')) {
            DB::statement("ALTER TABLE `users` ADD COLUMN `new_office_id` CHAR(36) NULL AFTER `address`");
        }
        if (!Schema::hasColumn('users', 'new_department_id')) {
            DB::statement("ALTER TABLE `users` ADD COLUMN `new_department_id` CHAR(36) NULL AFTER `new_office_id`");
        }
        foreach ($officeMap as $oldId => $newUuid) {
            DB::table('users')->where('office_id', $oldId)->update(['new_office_id' => $newUuid]);
        }
        foreach ($departmentMap as $oldId => $newUuid) {
            DB::table('users')->where('department_id', $oldId)->update(['new_department_id' => $newUuid]);
        }

        // 5. Update foreign keys in products
        if (!Schema::hasColumn('products', 'new_classification_id')) {
            DB::statement("ALTER TABLE `products` ADD COLUMN `new_classification_id` CHAR(36) NULL AFTER `description`");
        }
        if (!Schema::hasColumn('products', 'new_department_id')) {
            DB::statement("ALTER TABLE `products` ADD COLUMN `new_department_id` CHAR(36) NULL AFTER `new_classification_id`");
        }
        foreach ($classificationMap as $oldId => $newUuid) {
            DB::table('products')->where('classification_id', $oldId)->update(['new_classification_id' => $newUuid]);
        }
        foreach ($departmentMap as $oldId => $newUuid) {
            DB::table('products')->where('department_id', $oldId)->update(['new_department_id' => $newUuid]);
        }

        // 6. Update foreign keys in product_histories
        if (!Schema::hasColumn('product_histories', 'new_product_id')) {
            DB::statement("ALTER TABLE `product_histories` ADD COLUMN `new_product_id` CHAR(36) NULL");
        }
        if (!Schema::hasColumn('product_histories', 'new_dioper_oleh')) {
            DB::statement("ALTER TABLE `product_histories` ADD COLUMN `new_dioper_oleh` CHAR(36) NULL");
        }
        if (!Schema::hasColumn('product_histories', 'new_diterima_oleh')) {
            DB::statement("ALTER TABLE `product_histories` ADD COLUMN `new_diterima_oleh` CHAR(36) NULL");
        }
        if (!Schema::hasColumn('product_histories', 'new_pihak_pertama_id')) {
            DB::statement("ALTER TABLE `product_histories` ADD COLUMN `new_pihak_pertama_id` CHAR(36) NULL");
        }
        foreach ($productMap as $oldId => $newUuid) {
            DB::table('product_histories')->where('product_id', $oldId)->update(['new_product_id' => $newUuid]);
        }
        foreach ($userMap as $oldId => $newUuid) {
            DB::table('product_histories')->where('dioper_oleh', $oldId)->update(['new_dioper_oleh' => $newUuid]);
            DB::table('product_histories')->where('diterima_oleh', $oldId)->update(['new_diterima_oleh' => $newUuid]);
            DB::table('product_histories')->where('pihak_pertama_id', $oldId)->update(['new_pihak_pertama_id' => $newUuid]);
        }

        // 7. Update foreign keys in asset_requests
        if (!Schema::hasColumn('asset_requests', 'new_user_id')) {
            DB::statement("ALTER TABLE `asset_requests` ADD COLUMN `new_user_id` CHAR(36) NULL");
        }
        if (!Schema::hasColumn('asset_requests', 'new_department_id')) {
            DB::statement("ALTER TABLE `asset_requests` ADD COLUMN `new_department_id` CHAR(36) NULL");
        }
        foreach ($userMap as $oldId => $newUuid) {
            DB::table('asset_requests')->where('user_id', $oldId)->update(['new_user_id' => $newUuid]);
        }
        foreach ($departmentMap as $oldId => $newUuid) {
            DB::table('asset_requests')->where('department_id', $oldId)->update(['new_department_id' => $newUuid]);
        }

        // 8. Update foreign keys in asset_request_items
        if (!Schema::hasColumn('asset_request_items', 'new_asset_request_id')) {
            DB::statement("ALTER TABLE `asset_request_items` ADD COLUMN `new_asset_request_id` CHAR(36) NULL");
        }
        if (!Schema::hasColumn('asset_request_items', 'new_classification_id')) {
            DB::statement("ALTER TABLE `asset_request_items` ADD COLUMN `new_classification_id` CHAR(36) NULL");
        }
        foreach ($assetRequestMap as $oldId => $newUuid) {
            DB::table('asset_request_items')->where('asset_request_id', $oldId)->update(['new_asset_request_id' => $newUuid]);
        }
        foreach ($classificationMap as $oldId => $newUuid) {
            DB::table('asset_request_items')->where('classification_id', $oldId)->update(['new_classification_id' => $newUuid]);
        }

        // 9. Update foreign keys in asset_request_approvals
        if (!Schema::hasColumn('asset_request_approvals', 'new_asset_request_id')) {
            DB::statement("ALTER TABLE `asset_request_approvals` ADD COLUMN `new_asset_request_id` CHAR(36) NULL");
        }
        if (!Schema::hasColumn('asset_request_approvals', 'new_user_id')) {
            DB::statement("ALTER TABLE `asset_request_approvals` ADD COLUMN `new_user_id` CHAR(36) NULL");
        }
        foreach ($assetRequestMap as $oldId => $newUuid) {
            DB::table('asset_request_approvals')->where('asset_request_id', $oldId)->update(['new_asset_request_id' => $newUuid]);
        }
        foreach ($userMap as $oldId => $newUuid) {
            DB::table('asset_request_approvals')->where('user_id', $oldId)->update(['new_user_id' => $newUuid]);
        }

        // 10. Update foreign keys in ewtrs & ewtr_items
        if (!Schema::hasColumn('ewtrs', 'new_created_by')) {
            DB::statement("ALTER TABLE `ewtrs` ADD COLUMN `new_created_by` CHAR(36) NULL");
        }
        foreach ($userMap as $oldId => $newUuid) {
            DB::table('ewtrs')->where('created_by', $oldId)->update(['new_created_by' => $newUuid]);
        }

        if (!Schema::hasColumn('ewtr_items', 'new_completed_by')) {
            DB::statement("ALTER TABLE `ewtr_items` ADD COLUMN `new_completed_by` CHAR(36) NULL");
        }
        foreach ($userMap as $oldId => $newUuid) {
            DB::table('ewtr_items')->where('completed_by', $oldId)->update(['new_completed_by' => $newUuid]);
        }

        // 11. Update sessions and personal_access_tokens
        if (!Schema::hasColumn('sessions', 'new_user_id')) {
            DB::statement("ALTER TABLE `sessions` ADD COLUMN `new_user_id` CHAR(36) NULL");
        }
        foreach ($userMap as $oldId => $newUuid) {
            DB::table('sessions')->where('user_id', $oldId)->update(['new_user_id' => $newUuid]);
        }

        if (!Schema::hasColumn('personal_access_tokens', 'new_tokenable_id')) {
            DB::statement("ALTER TABLE `personal_access_tokens` ADD COLUMN `new_tokenable_id` CHAR(36) NULL");
        }
        foreach ($userMap as $oldId => $newUuid) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->where('tokenable_id', $oldId)
                ->update(['new_tokenable_id' => $newUuid]);
        }

        $convertPk = function($table) {
            if (Schema::hasColumn($table, 'uuid') && Schema::hasColumn($table, 'id')) {
                DB::statement("ALTER TABLE `$table` MODIFY `id` bigint unsigned NOT NULL");
                DB::statement("ALTER TABLE `$table` DROP PRIMARY KEY");
                DB::statement("ALTER TABLE `$table` DROP COLUMN `id`");
                DB::statement("ALTER TABLE `$table` CHANGE COLUMN `uuid` `id` CHAR(36) NOT NULL FIRST");
                DB::statement("ALTER TABLE `$table` ADD PRIMARY KEY (`id`)");
            }
        };

        // 12. Switch PKs & FKs on each table
        $convertPk('offices');
        $convertPk('classifications');
        $convertPk('departments');

        $convertPk('users');
        if (Schema::hasColumn('users', 'new_office_id')) {
            DB::statement("ALTER TABLE `users` DROP COLUMN `office_id`, CHANGE COLUMN `new_office_id` `office_id` CHAR(36) NULL");
        }
        if (Schema::hasColumn('users', 'new_department_id')) {
            DB::statement("ALTER TABLE `users` DROP COLUMN `department_id`, CHANGE COLUMN `new_department_id` `department_id` CHAR(36) NULL");
        }

        $convertPk('products');
        if (Schema::hasColumn('products', 'new_classification_id')) {
            DB::statement("ALTER TABLE `products` DROP COLUMN `classification_id`, CHANGE COLUMN `new_classification_id` `classification_id` CHAR(36) NOT NULL");
        }
        if (Schema::hasColumn('products', 'new_department_id')) {
            DB::statement("ALTER TABLE `products` DROP COLUMN `department_id`, CHANGE COLUMN `new_department_id` `department_id` CHAR(36) NOT NULL");
        }

        $convertPk('product_histories');
        if (Schema::hasColumn('product_histories', 'new_product_id')) {
            DB::statement("ALTER TABLE `product_histories` DROP COLUMN `product_id`, CHANGE COLUMN `new_product_id` `product_id` CHAR(36) NOT NULL");
        }
        if (Schema::hasColumn('product_histories', 'new_dioper_oleh')) {
            DB::statement("ALTER TABLE `product_histories` DROP COLUMN `dioper_oleh`, CHANGE COLUMN `new_dioper_oleh` `dioper_oleh` CHAR(36) NULL");
        }
        if (Schema::hasColumn('product_histories', 'new_diterima_oleh')) {
            DB::statement("ALTER TABLE `product_histories` DROP COLUMN `diterima_oleh`, CHANGE COLUMN `new_diterima_oleh` `diterima_oleh` CHAR(36) NULL");
        }
        if (Schema::hasColumn('product_histories', 'new_pihak_pertama_id')) {
            DB::statement("ALTER TABLE `product_histories` DROP COLUMN `pihak_pertama_id`, CHANGE COLUMN `new_pihak_pertama_id` `pihak_pertama_id` CHAR(36) NULL");
        }

        $convertPk('asset_requests');
        if (Schema::hasColumn('asset_requests', 'new_user_id')) {
            DB::statement("ALTER TABLE `asset_requests` DROP COLUMN `user_id`, CHANGE COLUMN `new_user_id` `user_id` CHAR(36) NOT NULL");
        }
        if (Schema::hasColumn('asset_requests', 'new_department_id')) {
            DB::statement("ALTER TABLE `asset_requests` DROP COLUMN `department_id`, CHANGE COLUMN `new_department_id` `department_id` CHAR(36) NOT NULL");
        }

        $convertPk('asset_request_items');
        if (Schema::hasColumn('asset_request_items', 'new_asset_request_id')) {
            DB::statement("ALTER TABLE `asset_request_items` DROP COLUMN `asset_request_id`, CHANGE COLUMN `new_asset_request_id` `asset_request_id` CHAR(36) NOT NULL");
        }
        if (Schema::hasColumn('asset_request_items', 'new_classification_id')) {
            DB::statement("ALTER TABLE `asset_request_items` DROP COLUMN `classification_id`, CHANGE COLUMN `new_classification_id` `classification_id` CHAR(36) NOT NULL");
        }

        $convertPk('asset_request_approvals');
        if (Schema::hasColumn('asset_request_approvals', 'new_asset_request_id')) {
            DB::statement("ALTER TABLE `asset_request_approvals` DROP COLUMN `asset_request_id`, CHANGE COLUMN `new_asset_request_id` `asset_request_id` CHAR(36) NOT NULL");
        }
        if (Schema::hasColumn('asset_request_approvals', 'new_user_id')) {
            DB::statement("ALTER TABLE `asset_request_approvals` DROP COLUMN `user_id`, CHANGE COLUMN `new_user_id` `user_id` CHAR(36) NOT NULL");
        }

        if (Schema::hasColumn('ewtrs', 'new_created_by')) {
            DB::statement("ALTER TABLE `ewtrs` DROP COLUMN `created_by`, CHANGE COLUMN `new_created_by` `created_by` CHAR(36) NULL");
        }

        if (Schema::hasColumn('ewtr_items', 'new_completed_by')) {
            DB::statement("ALTER TABLE `ewtr_items` DROP COLUMN `completed_by`, CHANGE COLUMN `new_completed_by` `completed_by` CHAR(36) NULL");
        }

        if (Schema::hasColumn('sessions', 'new_user_id')) {
            DB::statement("ALTER TABLE `sessions` DROP COLUMN `user_id`, CHANGE COLUMN `new_user_id` `user_id` CHAR(36) NULL");
            DB::statement("ALTER TABLE `sessions` ADD INDEX `sessions_user_id_index` (`user_id`)");
        }

        try {
            DB::statement("ALTER TABLE `personal_access_tokens` DROP INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`");
        } catch (\Exception $e) {}
        $convertPk('personal_access_tokens');
        if (Schema::hasColumn('personal_access_tokens', 'new_tokenable_id')) {
            DB::statement("ALTER TABLE `personal_access_tokens` DROP COLUMN `tokenable_id`, CHANGE COLUMN `new_tokenable_id` `tokenable_id` CHAR(36) NOT NULL");
            DB::statement("ALTER TABLE `personal_access_tokens` ADD INDEX `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)");
        }

        // 13. Re-add Foreign Key Constraints
        DB::statement("ALTER TABLE `users` ADD CONSTRAINT `users_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices`(`id`) ON DELETE SET NULL");
        DB::statement("ALTER TABLE `users` ADD CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL");

        DB::statement("ALTER TABLE `products` ADD CONSTRAINT `products_classification_id_foreign` FOREIGN KEY (`classification_id`) REFERENCES `classifications`(`id`) ON DELETE CASCADE");
        DB::statement("ALTER TABLE `products` ADD CONSTRAINT `products_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE");

        DB::statement("ALTER TABLE `product_histories` ADD CONSTRAINT `product_histories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE");
        DB::statement("ALTER TABLE `product_histories` ADD CONSTRAINT `product_histories_dioper_oleh_foreign` FOREIGN KEY (`dioper_oleh`) REFERENCES `users`(`id`) ON DELETE SET NULL");
        DB::statement("ALTER TABLE `product_histories` ADD CONSTRAINT `product_histories_diterima_oleh_foreign` FOREIGN KEY (`diterima_oleh`) REFERENCES `users`(`id`) ON DELETE SET NULL");
        DB::statement("ALTER TABLE `product_histories` ADD CONSTRAINT `product_histories_pihak_pertama_id_foreign` FOREIGN KEY (`pihak_pertama_id`) REFERENCES `users`(`id`) ON DELETE SET NULL");

        DB::statement("ALTER TABLE `asset_requests` ADD CONSTRAINT `asset_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
        DB::statement("ALTER TABLE `asset_requests` ADD CONSTRAINT `asset_requests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE");

        DB::statement("ALTER TABLE `asset_request_items` ADD CONSTRAINT `asset_request_items_asset_request_id_foreign` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests`(`id`) ON DELETE CASCADE");
        DB::statement("ALTER TABLE `asset_request_items` ADD CONSTRAINT `asset_request_items_classification_id_foreign` FOREIGN KEY (`classification_id`) REFERENCES `classifications`(`id`) ON DELETE CASCADE");

        DB::statement("ALTER TABLE `asset_request_approvals` ADD CONSTRAINT `asset_request_approvals_asset_request_id_foreign` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests`(`id`) ON DELETE CASCADE");
        DB::statement("ALTER TABLE `asset_request_approvals` ADD CONSTRAINT `asset_request_approvals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");

        DB::statement("ALTER TABLE `ewtrs` ADD CONSTRAINT `ewtrs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL");
        DB::statement("ALTER TABLE `ewtr_items` ADD CONSTRAINT `ewtr_items_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL");

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal of UUID to auto-increment is not practical without data loss or re-sequencing.
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->after('id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('serial_number')->nullable();
            $table->string('processor')->nullable();
            $table->string('ram')->nullable();
            $table->string('new_ram')->nullable();
            $table->string('ssd')->nullable();
            $table->string('new_ssd')->nullable();
            $table->string('os')->nullable();
            $table->string('screen_id')->nullable();
            $table->string('imei')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('device_class')->nullable();
        });

        Schema::table('product_histories', function (Blueprint $table) {
            $table->timestamp('lend_date')->nullable();
            $table->timestamp('returned_date')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('employee_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'serial_number', 'processor', 'ram', 'new_ram',
                'ssd', 'new_ssd', 'os', 'screen_id',
                'imei', 'mac_address', 'device_class'
            ]);
        });

        Schema::table('product_histories', function (Blueprint $table) {
            $table->dropColumn(['lend_date', 'returned_date', 'description', 'remarks']);
        });
    }
};

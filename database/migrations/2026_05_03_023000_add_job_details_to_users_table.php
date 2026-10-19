<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('job_position')->nullable()->after('role');
            $table->string('job_level')->nullable()->after('job_position');
            $table->date('join_date')->nullable()->after('job_level');
            $table->string('phone_number')->nullable()->after('join_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['job_position', 'job_level', 'join_date', 'phone_number']);
        });
    }
};

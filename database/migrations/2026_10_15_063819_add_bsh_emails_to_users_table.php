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
            if (!Schema::hasColumn('users', 'email_bsh')) {
                $table->string('email_bsh')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'personal_email')) {
                $table->string('personal_email')->nullable()->after('email_bsh');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email_bsh')) {
                $table->dropColumn('email_bsh');
            }
            if (Schema::hasColumn('users', 'personal_email')) {
                $table->dropColumn('personal_email');
            }
        });
    }
};

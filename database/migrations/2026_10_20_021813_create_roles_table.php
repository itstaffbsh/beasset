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
        // 1. Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // 2. Change users.role from ENUM to VARCHAR(255)
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) DEFAULT 'user'");

        // 3. Define all menus & all permissions for default seeding
        $menus = [
            'dashboard' => ['view'],
            'users' => ['view', 'tambah', 'edit', 'delete', 'import', 'export'],
            'offices' => ['view', 'tambah', 'edit', 'delete'],
            'classifications' => ['view', 'tambah', 'edit', 'delete'],
            'departments' => ['view', 'tambah', 'edit', 'delete'],
            'ewtr-categories' => ['view', 'tambah', 'edit', 'delete'],
            'products' => ['view', 'tambah', 'edit', 'delete', 'import', 'export'],
            'transfer' => ['view', 'tambah'],
            'history' => ['view', 'edit', 'delete'],
            'borrow-status' => ['view', 'tambah', 'edit'],
            'ewtr' => ['view', 'tambah', 'edit', 'delete'],
            'roles' => ['view', 'tambah', 'edit', 'delete'],
        ];

        $now = now();

        // Seed super_admin role (All permissions)
        DB::table('roles')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'description' => 'Akses penuh tanpa batas ke semua menu dan fitur sistem.',
            'permissions' => json_encode($menus),
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Seed admin role (Almost all permissions, roles management included)
        DB::table('roles')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'admin',
            'display_name' => 'Admin',
            'description' => 'Akses pengelolaan aset, pengguna, departemen, dan konfigurasi.',
            'permissions' => json_encode($menus),
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Seed user role (Standard user permissions)
        $userPermissions = [
            'dashboard' => ['view'],
            'products' => ['view'],
            'transfer' => ['view', 'tambah'],
            'history' => ['view'],
            'borrow-status' => ['view'],
        ];

        DB::table('roles')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'user',
            'display_name' => 'User / Karyawan',
            'description' => 'Pengguna standar dengan hak melihat aset, riwayat, dan meminjam aset.',
            'permissions' => json_encode($userPermissions),
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'user', 'manager_dept', 'managing_director') DEFAULT 'user'");
    }
};

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $office1 = \App\Models\Office::create(['nama_kantor' => 'Kantor Pusat', 'alamat' => 'Jl. Jend. Sudirman No 1']);
        $office2 = \App\Models\Office::create(['nama_kantor' => 'Kantor Cabang', 'alamat' => 'Jl. Merdeka No 2']);

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'office_id' => $office1->id,
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'office_id' => $office1->id,
        ]);

        User::factory()->create([
            'name' => 'Budi Peminjam',
            'email' => 'budi@admin.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'office_id' => $office2->id,
        ]);

        \App\Models\Department::create(['nama_departemen' => 'Accounting', 'kode_asset' => 'AC']);
        \App\Models\Department::create(['nama_departemen' => 'Human Resource', 'kode_asset' => 'HR']);
        \App\Models\Department::create(['nama_departemen' => 'Information Technology', 'kode_asset' => 'IT']);

        \App\Models\Classification::create(['nama_klasifikasi' => 'Elektronik']);
        \App\Models\Classification::create(['nama_klasifikasi' => 'Laptop']);
        \App\Models\Classification::create(['nama_klasifikasi' => 'Furniture']);
    }
}

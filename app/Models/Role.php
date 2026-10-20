<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Role extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_system',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    /**
     * Definisi semua menu di aplikasi beserta aksi yang didukung.
     */
    public static function getMenuDefinitions(): array
    {
        return [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'category' => 'Dashboard',
                'has_import_export' => false,
                'actions' => ['view'],
            ],
            [
                'key' => 'users',
                'label' => 'Karyawan (Users)',
                'category' => 'Master Data',
                'has_import_export' => true,
                'actions' => ['view', 'tambah', 'edit', 'delete', 'import', 'export', 'make_admin', 'reset_password'],
            ],
            [
                'key' => 'offices',
                'label' => 'Kantor & Lokasi',
                'category' => 'Master Data',
                'has_import_export' => false,
                'actions' => ['view', 'tambah', 'edit', 'delete'],
            ],
            [
                'key' => 'classifications',
                'label' => 'Kategori Aset',
                'category' => 'Master Data',
                'has_import_export' => false,
                'actions' => ['view', 'tambah', 'edit', 'delete'],
            ],
            [
                'key' => 'departments',
                'label' => 'Departemen',
                'category' => 'Master Data',
                'has_import_export' => false,
                'actions' => ['view', 'tambah', 'edit', 'delete'],
            ],
            [
                'key' => 'ewtr-categories',
                'label' => 'Kategori Tools EWTR',
                'category' => 'Master Data',
                'has_import_export' => false,
                'actions' => ['view', 'tambah', 'edit', 'delete'],
            ],
            [
                'key' => 'products',
                'label' => 'Daftar Inventaris (Aset)',
                'category' => 'Aset & Transaksi',
                'has_import_export' => true,
                'actions' => ['view', 'tambah', 'edit', 'delete', 'import', 'export'],
            ],
            [
                'key' => 'transfer',
                'label' => 'Pinjam / Kembali Aset',
                'category' => 'Aset & Transaksi',
                'has_import_export' => false,
                'actions' => ['view', 'tambah'],
            ],
            [
                'key' => 'history',
                'label' => 'Riwayat Mutasi',
                'category' => 'Aset & Transaksi',
                'has_import_export' => false,
                'actions' => ['view', 'edit', 'delete'],
            ],
            [
                'key' => 'borrow-status',
                'label' => 'Status Peminjaman',
                'category' => 'Aset & Transaksi',
                'has_import_export' => false,
                'actions' => ['view', 'tambah', 'edit'],
            ],
            [
                'key' => 'ewtr',
                'label' => 'Status EWTR',
                'category' => 'Aset & Transaksi',
                'has_import_export' => false,
                'actions' => ['view', 'tambah', 'edit', 'delete'],
            ],
            [
                'key' => 'roles',
                'label' => 'Manajemen Role',
                'category' => 'Pengaturan & Role',
                'has_import_export' => false,
                'actions' => ['view', 'tambah', 'edit', 'delete'],
            ],
        ];
    }

    /**
     * Mengembalikan mapping permissions lengkap (semua menu & semua aksi)
     */
    public static function getAllPermissionsList(): array
    {
        $perms = [];
        foreach (self::getMenuDefinitions() as $menu) {
            $perms[$menu['key']] = $menu['actions'];
        }
        return $perms;
    }
}

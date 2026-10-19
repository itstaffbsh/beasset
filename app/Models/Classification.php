<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* |--------------------------------------------------------------------------
   | [MODEL KLASIFIKASI]
   |--------------------------------------------------------------------------
   | Kegunaan: Sebagai daftar kategori barang (seperti Laptop, Monitor, dll).
   */

class Classification extends Model
{
    use HasFactory, HasUuids;

    /* | [ARRAY] | Kolom yang boleh diisi */
    protected $fillable = [
        'nama_klasifikasi',
    ];

    /* | [ARRAY/KOLEKSI] | Relasi: Satu kategori punya banyak aset */
    public function products()
    {
        return $this->hasMany(Aset::class);
    }
}

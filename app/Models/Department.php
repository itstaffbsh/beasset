<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* |--------------------------------------------------------------------------
   | [MODEL DEPARTEMEN]
   |--------------------------------------------------------------------------
   | Kegunaan: Sebagai daftar departemen (seperti IT, HR, ACCT, dll).
   */

class Department extends Model
{
    use HasFactory, HasUuids;

    /* | [ARRAY] | Kolom yang boleh diisi */
    protected $fillable = [
        'nama_departemen',
        'kode_asset',
    ];

    /* | [ARRAY/KOLEKSI] | Relasi: Satu departemen punya banyak aset */
    public function products()
    {
        return $this->hasMany(Aset::class);
    }
}

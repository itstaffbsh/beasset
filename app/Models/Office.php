<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* |--------------------------------------------------------------------------
   | [MODEL KANTOR]
   |--------------------------------------------------------------------------
   | Kegunaan: Sebagai daftar lokasi kantor (seperti Head Office, Cabang, dll).
   */
class Office extends Model
{
    use HasFactory, HasUuids;

    /* | [ARRAY] | Kolom yang boleh diisi */
    protected $fillable = [
        'nama_kantor',
        'alamat',
    ];

    /* | [ARRAY/KOLEKSI] | Relasi: Satu kantor punya banyak karyawan (users) */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}

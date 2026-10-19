<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/* |--------------------------------------------------------------------------
   | [MODEL ASET]
   |--------------------------------------------------------------------------
   | Kegunaan: Sebagai representasi data satu barang/aset di dalam database.
   */

class Aset extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /* | [KONFIGURASI] | Nama tabel di database tetap 'products' agar data tidak hilang */
    protected $table = 'products';

    /* | [KONSTRUKTOR] | Daftar kelas perangkat (Kategori Aset) */
    const DEVICE_CLASSES = ['1A', '1B', '2A', '2B', '3A', '3B'];

    /* | [ARRAY] | Daftar kolom yang boleh diisi secara massal (Security) */
    protected $fillable = [
        'description',
        'classification_id',
        'department_id',
        'nomor_unik',
        'harga',
        'url_token',
        'serial_number',
        'processor',
        'ram',
        'new_ram',
        'ssd',
        'new_ssd',
        'os',
        'screen_id',
        'imei',
        'mac_address',
        'device_class',
        'device_name',
        'has_headset',
        'remarks',
        'deletion_reason',
        'selling_price',
    ];

    protected $appends = ['full_nomor_unik'];

    /* | [FUNGSI RELASI] | Hubungan ke Departemen (Satu aset dimiliki oleh satu departemen) */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /* | [FUNGSI RELASI] | Hubungan ke Klasifikasi (Kategori barang seperti Laptop/PC) */
    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }

    /* | [ARRAY RELASI] | Hubungan ke Riwayat Mutasi (Satu aset bisa punya banyak riwayat pinjam) */
    public function histories()
    {
        return $this->hasMany(ProductHistory::class, 'product_id')->latest('created_at');
    }

    /* | [FUNGSI DINAMIS] | Menggabungkan Kode Departemen dan Nomor Unik (Contoh: HR-001) 
       | Kegunaan: Menampilkan label aset yang rapi di layar.
    */
    public function getFullNomorUnikAttribute()
    {
        return ($this->department->kode_asset ?? '??') . '-' . $this->nomor_unik;
    }
}

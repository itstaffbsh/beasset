<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    const DEVICE_CLASSES = ['1A', '1B', '1C', '2A', '2B', '2C', '3A', '3B', '3C'];

    protected $fillable = [
        'nama_asset',
        'classification_id',
        'department_id',
        'nomor_unik',
        'harga',
        'deletion_reason',
        'selling_price',
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
        'remarks',
    ];

    protected $appends = ['full_nomor_unik'];

    public function getFullNomorUnikAttribute()
    {
        if ($this->relationLoaded('department') && $this->department) {
            return $this->department->kode_asset . '-' . $this->nomor_unik;
        }
        return $this->nomor_unik;
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function histories()
    {
        return $this->hasMany(ProductHistory::class);
    }
}

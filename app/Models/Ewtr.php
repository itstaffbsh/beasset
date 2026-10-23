<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Ewtr extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nomor_ewtr',
        'pdf_path',
        'pdf_name',
        'keterangan',
        'status',
        'created_by',
    ];

    protected $appends = [
        'pdf_url',
        'total_items_count',
        'completed_items_count',
    ];

    public function items()
    {
        return $this->hasMany(EwtrItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPdfUrlAttribute()
    {
        if (!$this->pdf_path) return null;
        $cleanPath = ltrim(preg_replace('/^\/?storage\/?/', '', $this->pdf_path), '/');
        return url('storage/' . $cleanPath);
    }

    public function getTotalItemsCountAttribute()
    {
        return $this->items()->count();
    }

    public function getCompletedItemsCountAttribute()
    {
        return $this->items()->where('is_completed', true)->count();
    }

    public function syncStatus()
    {
        $total = $this->items()->count();
        $completed = $this->items()->where('is_completed', true)->count();

        if ($total > 0 && $total === $completed) {
            $this->status = 'sukses';
        } else {
            $this->status = 'belum_sukses';
        }
        $this->save();

        return $this;
    }
}

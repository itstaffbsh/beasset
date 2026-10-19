<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EwtrItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ewtr_id',
        'ewtr_tool_category_id',
        'item_name',
        'is_completed',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function ewtr()
    {
        return $this->belongsTo(Ewtr::class);
    }

    public function toolCategory()
    {
        return $this->belongsTo(EwtrToolCategory::class, 'ewtr_tool_category_id');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EwtrToolCategory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
    ];

    public function ewtrItems()
    {
        return $this->hasMany(EwtrItem::class);
    }
}

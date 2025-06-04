<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'model',
        'image'
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(GiftPrice::class);
    }
} 
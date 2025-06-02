<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gift extends Model
{
    protected $fillable = [
        'name',
        'model'
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(GiftPrice::class);
    }
} 
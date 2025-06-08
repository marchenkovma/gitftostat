<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftPrice extends Model
{
    protected $fillable = [
        'gift_id',
        'price',
        'checked_at'
    ];

    protected $casts = [
        'checked_at' => 'datetime'
    ];

    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftPrice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gift_id',
        'price',
        'checked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'checked_at' => 'datetime',
    ];

    /**
     * Get the gift that owns the price.
     */
    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }
} 
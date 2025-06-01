<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gift extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'model',
    ];

    /**
     * Get the prices for the gift.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(GiftPrice::class);
    }

    /**
     * Get the latest price for the gift.
     */
    public function getLatestPriceAttribute()
    {
        return $this->prices()->latest('checked_at')->first()?->price;
    }

    /**
     * Get the price attribute.
     */
    public function getPriceAttribute()
    {
        return $this->latest_price;
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Charger extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'identifier',
        'status',
        'price_per_kwh',
        'last_heartbeat',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

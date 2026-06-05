<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Charger extends Model
{
    public $timestamps = false;

    protected $casts = [
        'last_heartbeat' => 'datetime',
    ];
    
    protected $fillable = [
        'identifier',
        'name',
        'location',
        'status',
        'price_per_kwh',
        'last_heartbeat',
    ];

    protected function casts(): array
    {
        return [
            'price_per_kwh' => MoneyCast::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'identifier';
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

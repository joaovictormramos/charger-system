<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    public const CREATED_AT = 'start_time';
 
    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    
    public const UPDATED_AT = null;

        protected $fillable = [
        'charger_id',
        'rfid_card_id', 
        'meter_start',
        'meter_stop',
        'energy_kwh',
        'total_cost',
        'paid_amount',
        'stop_reason',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'total_cost' => MoneyCast::class,
            'paid_amount' => MoneyCast::class,
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function charger(): BelongsTo
    {
        return $this->belongsTo(Charger::class);
    }

    public function rfidCard(): BelongsTo
    {
        return $this->belongsTo(RfidCard::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($transaction) {
            $transaction->uuid = Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

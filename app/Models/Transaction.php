<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'stop_reason',
        'end_time',
    ];

    public function charger(): BelongsTo
    {
        return $this->belongsTo(Charger::class);
    }

    public function rfidCard(): BelongsTo
    {
        return $this->belongsTo(RfidCard::class);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Charger;
use App\Models\RfidCard;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $charger = Charger::where('identifier', 'TENDA-60K-1')->first();
        $rfidCard = RfidCard::first();

        // Sessão ativa (sem end_time)
        Transaction::create([
            'uuid' => Str::uuid(),
            'charger_id' => $charger->id,
            'rfid_card_id' => $rfidCard->id,
            'meter_start' => 100000,
            'energy_kwh' => 3.5,
            'total_cost' => 346,
            'paid_amount' => 5000, // R$ 50,00
            'start_time' => now()->subMinutes(28),
        ]);

        // Sessão finalizada
        Transaction::create([
            'uuid' => Str::uuid(),
            'charger_id' => $charger->id,
            'rfid_card_id' => $rfidCard->id,
            'meter_start' => 90000,
            'meter_stop' => 95050,
            'energy_kwh' => 5.05,
            'total_cost' => 500,
            'paid_amount' => 5000, // R$ 50,00
            'stop_reason' => 'Local',
            'start_time' => now()->subDays(3)->subMinutes(42),
            'end_time' => now()->subDays(3),
        ]);
    }
}

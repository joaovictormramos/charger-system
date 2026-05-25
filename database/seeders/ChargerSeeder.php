<?php

namespace Database\Seeders;

use App\Models\Charger;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChargerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Charger::create([
            'identifier' => 'TENDA-60K-1',
            'status' => 'Available',
            'price_per_kwh' => 99, // R$ 0,99
            'last_heartbeat' => now(),
        ]);

        Charger::create([
            'identifier' => 'TENDA-30K-1',
            'status' => 'Available',
            'price_per_kwh' => 99,
            'last_heartbeat' => now(),
        ]);

        Charger::create([
            'identifier' => 'TENDA-30K-2',
            'status' => 'Charging',
            'price_per_kwh' => 99,
            'last_heartbeat' => now(),
        ]);
    }
}

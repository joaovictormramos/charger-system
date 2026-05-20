<?php

namespace Database\Seeders;

use App\Models\RfidCard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RfidCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RfidCard::create([
            'uuid' => '1234-5678-9ABC-DEF0',
            'active' => true,
            'balance' => 4780, // R$ 47,80
        ]);

        RfidCard::create([
            'uuid' => 'ABCD-EF01-2345-6789',
            'active' => true,
            'balance' => 10000, // R$ 100,00
        ]);

        RfidCard::create([
            'uuid' => '0000-0000-0000-0001',
            'active' => false,
            'balance' => 0,
        ]);
    }
}

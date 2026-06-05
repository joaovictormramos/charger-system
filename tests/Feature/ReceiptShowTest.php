<?php

namespace Tests\Feature;

use App\Models\Charger;
use App\Models\RfidCard;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ReceiptShowTest extends TestCase
{
    use RefreshDatabase;

    private function makeCharger(): Charger
    {
        return Charger::create([
            'identifier' => 'TEST-01',
            'status' => 'Available',
            'price_per_kwh' => 0.99,
        ]);
    }

    private function makeCard(): RfidCard
    {
        return RfidCard::create([
            'uuid' => 'CARD-UID-001',
            'active' => true,
            'balance' => 50,
        ]);
    }

    private function makeTransaction(array $attrs = []): Transaction
    {
        $charger = $this->makeCharger();
        $card = $this->makeCard();

        return Transaction::create(array_merge([
            'uuid' => Str::uuid(),
            'charger_id' => $charger->id,
            'rfid_card_id' => $card->id,
            'meter_start' => 100000,
            'meter_stop' => 110000,
            'energy_kwh' => 10.0,
            'total_cost' => 9.90,
            'paid_amount' => 50.00,
            'stop_reason' => 'Local',
            'start_time' => now()->subHour(),
            'end_time' => now(),
        ], $attrs));
    }

    public function test_receipt_renders_without_errors(): void
    {
        $transaction = $this->makeTransaction();

        Livewire::test('receipt.receipt-show', ['transaction' => $transaction])
            ->assertOk()
            ->assertSee('Recarga concluída');
    }

    public function test_receipt_shows_charger_identifier(): void
    {
        $transaction = $this->makeTransaction();

        Livewire::test('receipt.receipt-show', ['transaction' => $transaction])
            ->assertSee('TEST-01');
    }

    public function test_receipt_shows_energy_kwh(): void
    {
        $transaction = $this->makeTransaction(['energy_kwh' => 10.0]);

        Livewire::test('receipt.receipt-show', ['transaction' => $transaction])
            ->assertSee('10,00');
    }

    public function test_receipt_shows_total_cost(): void
    {
        $transaction = $this->makeTransaction(['total_cost' => 9.90]);

        Livewire::test('receipt.receipt-show', ['transaction' => $transaction])
            ->assertSee('9,90');
    }

    public function test_receipt_shows_paid_amount(): void
    {
        $transaction = $this->makeTransaction(['paid_amount' => 50.00]);

        Livewire::test('receipt.receipt-show', ['transaction' => $transaction])
            ->assertSee('50,00');
    }

    public function test_receipt_shows_duration(): void
    {
        $start = \Carbon\Carbon::parse('2026-01-01 10:00:00');
        $end = \Carbon\Carbon::parse('2026-01-01 10:42:00');

        $transaction = $this->makeTransaction([
            'start_time' => $start,
            'end_time' => $end,
        ]);

        Livewire::test('receipt.receipt-show', ['transaction' => $transaction])
            ->assertSee('42min');
    }

    public function test_receipt_shows_rfid_fee_line_for_rfid_transaction(): void
    {
        $transaction = $this->makeTransaction();

        Livewire::test('receipt.receipt-show', ['transaction' => $transaction])
            ->assertDontSee('Taxa de plataforma');
    }

    public function test_receipt_shows_platform_fee_for_pix_transaction(): void
    {
        $charger = $this->makeCharger();

        $transaction = Transaction::create([
            'uuid' => Str::uuid(),
            'charger_id' => $charger->id,
            'rfid_card_id' => null,
            'meter_start' => 100000,
            'meter_stop' => 110000,
            'energy_kwh' => 10.0,
            'total_cost' => 9.90,
            'paid_amount' => 50.00,
            'stop_reason' => 'Local',
            'start_time' => now()->subHour(),
            'end_time' => now(),
        ]);

        Livewire::test('receipt.receipt-show', ['transaction' => $transaction])
            ->assertSee('Taxa de plataforma');
    }
}
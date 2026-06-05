<?php

namespace Tests\Feature;

use App\Models\Charger;
use App\Models\RfidCard;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SessionShowTest extends TestCase
{
    use RefreshDatabase;

    private function makeCharger(): Charger
    {
        return Charger::create([
            'identifier' => 'TEST-01',
            'status' => 'Charging',
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
            'energy_kwh' => 3.5,
            'total_cost' => 3.47,
            'paid_amount' => 50.00,
            'start_time' => now()->subMinutes(28),
        ], $attrs));
    }

    public function test_session_renders_without_errors(): void
    {
        $transaction = $this->makeTransaction();

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->assertOk()
            ->assertSee('Sessão ativa');
    }

    public function test_session_shows_charger_identifier(): void
    {
        $transaction = $this->makeTransaction();

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->assertSee('TEST-01');
    }

    public function test_session_shows_energy_kwh(): void
    {
        $transaction = $this->makeTransaction(['energy_kwh' => 3.5]);

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->assertSee('3,50');
    }

    public function test_session_shows_total_cost(): void
    {
        $transaction = $this->makeTransaction(['total_cost' => 3.47]);

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->assertSee('3,47');
    }

    public function test_session_shows_paid_amount(): void
    {
        $transaction = $this->makeTransaction(['paid_amount' => 50.00]);

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->assertSee('50,00');
    }

    public function test_session_shows_price_per_kwh(): void
    {
        $transaction = $this->makeTransaction();

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->assertSee('0,99');
    }

    public function test_session_shows_rfid_stop_message(): void
    {
        $transaction = $this->makeTransaction();

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->assertSee('cobrado apenas pelo que consumiu');
    }

    public function test_session_shows_pix_stop_message(): void
    {
        $charger = $this->makeCharger();

        $transaction = Transaction::create([
            'uuid' => Str::uuid(),
            'charger_id' => $charger->id,
            'rfid_card_id' => null,
            'meter_start' => 100000,
            'energy_kwh' => 3.5,
            'total_cost' => 3.47,
            'paid_amount' => 50.00,
            'start_time' => now()->subMinutes(28),
        ]);

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->assertSee('estornado automaticamente');
    }

    public function test_stop_session_redirects_to_receipt(): void
    {
        $transaction = $this->makeTransaction();

        Livewire::test('session.session-show', ['transaction' => $transaction])
            ->call('stopSession')
            ->assertRedirect(route('receipt.show', $transaction));
    }
}
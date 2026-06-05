<?php

namespace Tests\Feature;

use App\Models\Charger;
use App\Models\RfidCard;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CardShowTest extends TestCase
{
    use RefreshDatabase;

    private function makeCard(array $attrs = []): RfidCard
    {
        return RfidCard::create(array_merge([
            'uuid' => 'CARD-UID-001',
            'active' => true,
            'balance' => 50,
        ], $attrs));
    }

    private function makeCharger(): Charger
    {
        return Charger::create([
            'identifier' => 'TEST-01',
            'status' => 'Available',
            'price_per_kwh' => 0.99,
        ]);
    }

    private function makeTransaction(RfidCard $card, Charger $charger, bool $finished = true): Transaction
    {
        return Transaction::create([
            'uuid' => Str::uuid(),
            'charger_id' => $charger->id,
            'rfid_card_id' => $card->id,
            'meter_start' => 100000,
            'meter_stop' => $finished ? 110000 : null,
            'energy_kwh' => $finished ? 10.0 : null,
            'total_cost' => $finished ? 9.90 : null,
            'start_time' => now()->subHour(),
            'end_time' => $finished ? now() : null,
        ]);
    }

    public function test_card_page_renders_without_errors(): void
    {
        $card = $this->makeCard();

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->assertOk()
            ->assertSee('Saldo disponível');
    }

    public function test_card_shows_correct_balance(): void
    {
        $card = $this->makeCard(['balance' => 50]);

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->assertSee('50,00');
    }

    public function test_card_shows_last_four_of_uuid(): void
    {
        $card = $this->makeCard(['uuid' => 'ABCD-1234']);

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->assertSee('1234');
    }

    public function test_recharge_tab_is_default(): void
    {
        $card = $this->makeCard();

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->assertSet('tab', 'recharge');
    }

    public function test_can_switch_to_history_tab(): void
    {
        $card = $this->makeCard();

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->call('$set', 'tab', 'history')
            ->assertSet('tab', 'history');
    }

    public function test_history_tab_shows_transactions(): void
    {
        $card = $this->makeCard();
        $charger = $this->makeCharger();
        $this->makeTransaction($card, $charger);

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->call('$set', 'tab', 'history')
            ->assertSee('TEST-01');
    }

    public function test_history_tab_shows_empty_message_when_no_transactions(): void
    {
        $card = $this->makeCard();

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->call('$set', 'tab', 'history')
            ->assertSee('Nenhuma recarga ainda');
    }

    public function test_select_amount_updates_property(): void
    {
        $card = $this->makeCard();

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->call('selectAmount', 5000)
            ->assertSet('amount', 5000);
    }

    public function test_generate_pix_shows_pix_area(): void
    {
        $card = $this->makeCard();

        Livewire::test('card.card-show', ['rfidCard' => $card])
            ->call('selectAmount', 5000)
            ->call('generatePix')
            ->assertSet('showPix', true);
    }
}
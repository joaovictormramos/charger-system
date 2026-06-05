<?php

namespace Tests\Feature;

use App\Models\Charger;
use App\Models\RfidCard;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OcppControllerTest extends TestCase
{
    use RefreshDatabase;

    private function ocppPost(string $action, string $chargerId, array $payload): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/ocpp', [
            'action' => $action,
            'charger_id' => $chargerId,
            'payload' => $payload,
        ]);
    }

    private function makeCharger(array $attrs = []): Charger
    {
        return Charger::create(array_merge([
            'identifier' => 'TEST-01',
            'status' => 'Available',
            'price_per_kwh' => 0.99,
        ], $attrs));
    }

    private function makeCard(array $attrs = []): RfidCard
    {
        return RfidCard::create(array_merge([
            'uuid' => 'CARD-UID-001',
            'active' => true,
            'balance' => 50,
        ], $attrs));
    }

    // BootNotification
    public function test_boot_notification_accepts_and_updates_charger(): void
    {
        $charger = $this->makeCharger();

        $response = $this->ocppPost('BootNotification', $charger->identifier, [
            'chargePointModel' => 'TestModel',
            'chargePointVendor' => 'TestVendor',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'Accepted');

        $this->assertDatabaseHas('chargers', [
            'identifier' => 'TEST-01',
            'status' => 'Available',
        ]);
    }

    // Heartbeat
    public function test_heartbeat_updates_last_heartbeat(): void
    {
        $charger = $this->makeCharger();

        $response = $this->ocppPost('Heartbeat', $charger->identifier, []);

        $response->assertOk()
            ->assertJsonStructure(['currentTime']);

        $this->assertNotNull($charger->fresh()->last_heartbeat);
    }

    // Authorize
    public function test_authorize_accepts_valid_card(): void
    {
        $charger = $this->makeCharger();
        $card = $this->makeCard();

        $response = $this->ocppPost('Authorize', $charger->identifier, [
            'idTag' => $card->uuid,
        ]);

        $response->assertOk()
            ->assertJsonPath('idTagInfo.status', 'Accepted');
    }

    public function test_authorize_rejects_inactive_card(): void
    {
        $charger = $this->makeCharger();
        $card = $this->makeCard(['active' => false]);

        $response = $this->ocppPost('Authorize', $charger->identifier, [
            'idTag' => $card->uuid,
        ]);

        $response->assertOk()
            ->assertJsonPath('idTagInfo.status', 'Invalid');
    }

    public function test_authorize_rejects_card_with_insufficient_balance(): void
    {
        $charger = $this->makeCharger();
        $card = $this->makeCard(['balance' => 1]); // R$ 1,00 — abaixo do mínimo

        $response = $this->ocppPost('Authorize', $charger->identifier, [
            'idTag' => $card->uuid,
        ]);

        $response->assertOk()
            ->assertJsonPath('idTagInfo.status', 'Invalid');
    }

    // StartTransaction
    public function test_start_transaction_creates_transaction(): void
    {
        $charger = $this->makeCharger();
        $card = $this->makeCard();

        $response = $this->ocppPost('StartTransaction', $charger->identifier, [
            'idTag' => $card->uuid,
            'meterStart' => 100000,
            'timestamp' => now()->toIso8601String(),
        ]);

        $response->assertOk()
            ->assertJsonPath('idTagInfo.status', 'Accepted');

        $this->assertDatabaseHas('transactions', [
            'charger_id' => $charger->id,
            'rfid_card_id' => $card->id,
            'meter_start' => 100000,
        ]);
    }

    public function test_start_transaction_rejects_unknown_card(): void
    {
        $charger = $this->makeCharger();

        $response = $this->ocppPost('StartTransaction', $charger->identifier, [
            'idTag' => 'UNKNOWN-UID',
            'meterStart' => 100000,
            'timestamp' => now()->toIso8601String(),
        ]);

        $response->assertOk()
            ->assertJsonPath('transactionId', 0);
    }

    // StopTransaction
    public function test_stop_transaction_calculates_cost_and_decrements_balance(): void
    {
        $charger = $this->makeCharger(['price_per_kwh' => 0.99]);
        $card = $this->makeCard(['balance' => 50]);

        $transaction = Transaction::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'charger_id' => $charger->id,
            'rfid_card_id' => $card->id,
            'meter_start' => 100000,
        ]);

        // 10 kWh consumidos (110000 - 100000 = 10000 Wh = 10 kWh)
        $response = $this->ocppPost('StopTransaction', $charger->identifier, [
            'transactionId' => $transaction->id,
            'meterStop' => 110000,
            'timestamp' => now()->toIso8601String(),
            'reason' => 'Local',
        ]);

        $response->assertOk()
            ->assertJsonPath('idTagInfo.status', 'Accepted');

        // 10 kWh * R$ 0,99 = R$ 9,90 = 990 centavos
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'meter_stop' => 110000,
            'total_cost' => 990,
        ]);

        // Saldo: 5000 - 990 = 4010 centavos
        $this->assertEquals(40.10, $card->fresh()->balance);
    }

    public function test_stop_transaction_returns_invalid_for_unknown_transaction(): void
    {
        $charger = $this->makeCharger();

        $response = $this->ocppPost('StopTransaction', $charger->identifier, [
            'transactionId' => 99999,
            'meterStop' => 110000,
            'timestamp' => now()->toIso8601String(),
        ]);

        $response->assertOk()
            ->assertJsonPath('idTagInfo.status', 'Invalid');
    }

    // NotImplemented
    public function test_unknown_action_returns_not_implemented(): void
    {
        $response = $this->ocppPost('UnknownAction', 'TEST-01', []);

        $response->assertStatus(404)
            ->assertJsonPath('status', 'NotImplemented');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Charger;
use App\Models\RfidCard;
use App\Models\Tag;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OcppController extends Controller
{
    private const MINIMUM_BALANCE = 2000;
    private const ALLOWED_ACTIONS = [
        'Authorize',
        'BootNotification',
        'Heartbeat',
        'MeterValues',
        'StartTransaction',
        'StatusNotification',
        'StopTransaction'
    ];

    public function handle(Request $request)
    {
        $action = $request->input('action');
        $chargerId = $request->input('charger_id');
        $payload = $request->input('payload');

        if (in_array($action, self::ALLOWED_ACTIONS) && method_exists($this, $action)) {
            return $this->$action($chargerId, $payload);
        }

        return response()->json(['status' => 'NotImplemented'], 404);
    }

    private function Authorize($chargerId, $payload): array
    {
        $rfidCard = RfidCard::where('uuid', $payload['idTag'])->first();

        if ($rfidCard && $rfidCard->active && $rfidCard->balance >= self::MINIMUM_BALANCE) {
            return ['idTagInfo' =>
                [
                    'status' => 'Accepted'
                ]
            ];
        }

        return ['idTagInfo' =>
            [
                'status' => 'Invalid'
            ]
        ];
    }

    private function BootNotification($chargerId, $payload): array
    {
        Charger::where('identifier', $chargerId)->update([
            'status' => 'Available',
            'last_heartbeat' => now(),
        ]);
        
        return [
            'currentTime' => now(),
            'interval' => 60,
            'status' => 'Accepted'
        ];
    }

    private function Heartbeat($chargerId, $payload): array
    {
        Charger::where('identifier', $chargerId)->update([
            'last_heartbeat' => now(),
        ]);
        
        return [
            'currentTime' => now()
        ];
    }
     
    private function MeterValues($chargerId, $payload): array
    {
        $transactionId = $payload['transactionId'] ?? null;
     
        if (!$transactionId) {
           return [];
        }
        $meterValue = collect($payload['meterValue'])->last();

        $energySample = collect($meterValue['sampledValue'])
            ->firstWhere('measurand', 'Energy.Active.Import.Register');
        if (!$energySample) {
            return [];
        }

        $transaction = Transaction::find($transactionId);
        if (!$transaction) {
           return [];
        }

        $currentMeter = (int) $energySample['value'];
        $energyKwh = ($currentMeter - $transaction->meter_start) / 1000;

        $transaction->update([
            'energy_kwh' => $energyKwh,
        ]);

        return [];
    }

    private function RemoteStopTransaction($chargerId, $payload): array
    {
        return [];
    }

    private function StartTransaction($chargerId, $payload): array
    {
        $charger = Charger::where('identifier', $chargerId)->first();
        $rfidCard = RfidCard::where('uuid', $payload['idTag'])->first();
        $auth = $this->Authorize($chargerId, $payload);
    
        if (!$charger || !$rfidCard) {
            return [
                'idTagInfo' => ['status' => 'Invalid'],
                'transactionId' => 0
            ];
        }

        if ($auth['idTagInfo']['status'] !== 'Accepted') {
            return [
                'idTagInfo' => $auth['idTagInfo'],
                'transactionId' => 0
            ];
        }

        $transaction = Transaction::create([
            'charger_id' => $charger->id,
            'rfid_card_id' => $rfidCard->id,
            'meter_start' => $payload['meterStart']
        ]);
        
        return [
            'idTagInfo' => $auth['idTagInfo'],
            'transactionId' => $transaction->id
        ];
    }

    private function StatusNotification($chargerId, $payload): array
    {
        Charger::where('identifier', $chargerId)->update([
            'status' => $payload['status'],
        ]);

        return [];
    }

    private function StopTransaction($chargerId, $payload): array
    {
        $transaction = Transaction::find($payload['transactionId']);

        if (!$transaction) {
            return ['idTagInfo' => ['status' => 'Invalid']];
        }

        $charger  = Charger::find($transaction->charger_id);
        $rfidCard = RfidCard::find($transaction->rfid_card_id);

        $meterStop  = (int) $payload['meterStop'];
        $energyKwh  = ($meterStop - $transaction->meter_start) / 1000;
        $totalCost  = (int) round($energyKwh * $charger->price_per_kwh);

        $transaction->update([
            'meter_stop'    => $meterStop,
            'energy_kwh'    => $energyKwh,
            'total_cost'    => $totalCost,
            'end_time'      => $payload['timestamp'],
            'stop_reason'   => $payload['reason'] ?? 'Local',
        ]);

        if ($rfidCard) {
            $rfidCard->decrement('balance', $totalCost);
        }

        $charger->update(['status' => 'Available']);

        return ['idTagInfo' => ['status' => 'Accepted']];
    }
}

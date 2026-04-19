<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OcppController extends Controller
{
    public function handle(Request $request)
    {
        $action = $request->input('action');
        $chargerId = $request->input('charger_id');
        $payload = $request->input('payload');

        if (method_exists($this, $action)) {
            return $this->$action($chargerId, $payload);
        }

        return response()->json(['status' => 'NotImplemented'], 404);
    }

    private function BootNotification(): Response
    {
        return [
            'currentTime' => now(),
            'interval' => 60,
            'status' => 'Accepted'
        ]
    }

    private function HeartBeat(): Response
    {
        return [
        ]
    }

    private function ChangeNotification: Response
    {
        return [
        ];
    }

    private function Authorize($charger, $payload): Response
    {
        $tag = Tag::where('idTag', $payload['idTag'])->first();

        if ($tag && $tag->balance >= 2000) {
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
    }

    private function StartTransaction(): Response
    {
        $transaction = new Transaction::create([
            'charger_id' => $chargerId,
            'id_tag' => $payload['idTag'],
            'meter_start' => $payload['meterValue']
        ]);

        return [
            'idTagInfo' =>
                [
                    'status' $this->Authorize($charger, $payload),
                ],
            'transactionId' => $transaction->id
        ];
    }

    private function StopTransaction(): Response
    {
        return [
        ];
    }

    private function MeterValues(): Response
    {
        return [
        ];
    }
}

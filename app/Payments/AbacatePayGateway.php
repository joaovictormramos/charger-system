<?php

namespace App\Payments;

use Illuminate\Support\Facades\Http;

class AbacatePayGateway implements PaymentGateway
{
    private string $apiKey;
    private string $baseUrl = 'https://api.abacatepay.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.abacatepay.key');
    }

    public function createPixCharge(int $amountCents, string $reference): PixCharge
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/pixQrCode/create", [
                'amount' => $amountCents,
                'expiresIn' => 300, // 5 minutos
                'description' => "Recarga #{$reference}",
            ]);

        $data = $response->json();

        return new PixCharge(
            transactionId: $data['id'],
            qrCode: $data['brCode'],
            qrCodeBase64: $data['qrCode'],
            expiresAt: new \DateTime($data['expiresAt']),
        );
    }

    public function refund(string $gatewayTransactionId, int $amountCents): bool
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/pixQrCode/refund", [
                'id' => $gatewayTransactionId,
                'amount' => $amountCents,
            ]);

        return $response->successful();
    }
}
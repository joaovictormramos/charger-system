<?php

namespace App\Payments;

interface PaymentGateway
{
    public function createPixCharge(int $amountCents, string $reference): PixCharge;
    public function refund(string $gatewayTransactionId, int $amountCents): bool;
}
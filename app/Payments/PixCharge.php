<?php

namespace App\Payments;

class PixCharge
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $qrCode,
        public readonly string $qrCodeBase64,
        public readonly \DateTime $expiresAt,
    ) {}
}
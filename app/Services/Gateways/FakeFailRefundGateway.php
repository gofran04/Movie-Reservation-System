<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use App\Services\Contracts\RefundGatewayInterface;
use Illuminate\Support\Str;

class FakeFailRefundGateway implements RefundGatewayInterface
{
    public function refund(Payment $payment): array
    {
        throw new \Exception('Refund failed');
    }
}
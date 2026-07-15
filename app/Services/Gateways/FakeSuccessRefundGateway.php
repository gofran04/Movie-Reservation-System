<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use App\Services\Contracts\RefundGatewayInterface;
use Illuminate\Support\Str;

class FakeSuccessRefundGateway implements RefundGatewayInterface
{
    public function refund(Payment $payment): array
    {
        return [
            'reference_id' => 'fake_refund_' . Str::uuid(),
        ];
    }
}
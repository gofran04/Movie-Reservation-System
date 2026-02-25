<?php

namespace App\Services\Contracts;

use App\Models\Payment;

interface RefundGatewayInterface
{
    public function refund(Payment $payment);
}

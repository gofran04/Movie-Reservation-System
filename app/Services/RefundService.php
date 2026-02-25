<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Validation\ValidationException;
use App\Services\Contracts\RefundGatewayInterface;
class RefundService
{
    protected RefundGatewayInterface $gateWay;

    public function __construct(RefundGatewayInterface $gateWay) {
        $this->gateWay = $gateWay;
    }

    public function refund(Payment $payment): void
    {
        if ($payment->status !== 'succeeded') {
            throw ValidationException::withMessages([
                'payment' => ['Only successful payments can be refunded.'],
            ]);
        }

        try {
           $result =  $this->gateWay->refund($payment);

           $payment->update([
            'refund_reference' => $result['reference_id'],
            'status'           => 'refund_pending',
        ]);
        } catch (\Exception $e) {
            throw new \Exception('Refund failed: ' . $e->getMessage());
        }
    }
}

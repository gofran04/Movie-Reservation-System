<?php

namespace App\Services\Gateways\Stripe;

use App\Models\Payment;
use Illuminate\Validation\ValidationException;
use App\Services\Contracts\RefundGatewayInterface;
use Stripe\Stripe;

class StripeRefundGateway implements RefundGatewayInterface
{
    public function refund(Payment $payment)
    {
        if ($payment->status !== 'succeeded') {
            throw ValidationException::withMessages([
                'payment' => ['Only successful payments can be refunded.'],
            ]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $refund = \Stripe\Refund::create([
            'payment_intent' => $payment->stripe_payment_intent_id,
        ]);

        return [
                'reference_id' => $refund->id,
            ];
    }
}
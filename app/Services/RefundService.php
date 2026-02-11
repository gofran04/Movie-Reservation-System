<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Validation\ValidationException;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class RefundService
{
    public function refund(Payment $payment): void
    {
        if ($payment->status !== 'succeeded') {
            throw ValidationException::withMessages([
                'payment' => ['Only successful payments can be refunded.'],
            ]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        \Stripe\Refund::create([
            'payment_intent' => $payment->stripe_payment_intent_id,
        ]);

        $payment->update([
            'status' => 'refunded',
        ]);
    }
}

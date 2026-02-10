<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Reservation;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class ProcessPaymentService
{
    public function createCheckoutSession(Reservation $reservation): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Cinema Reservation #' . $reservation->id,
                    ],
                    'unit_amount' => (int) ($reservation->total_price * 100),//Convert dollars to cents. Stripe needs cents
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'reservation_id' => $reservation->id,
            ],
            'success_url' => 'https://example.com/success',
            'cancel_url'  => 'https://example.com/cancel'
        ]);

        Payment::create([
            'reservation_id' => $reservation->id,
            'stripe_checkout_session_id' => $session->id, // Store session ID for later reference
            'amount' => $reservation->total_price,
            'currency' => 'usd',
            'status' => 'pending',
        ]);

        return $session->url;
    }
}

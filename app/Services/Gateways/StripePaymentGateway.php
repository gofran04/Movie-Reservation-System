<?php

namespace App\Services\Gateways;

use App\Models\Reservation;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Services\Contracts\PaymentGatewayInterface;

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function pay(Reservation $reservation): string
    {
        /* 
            Used checkout session to create a payment session with stripe, and then store the session id in the database for later reference.
            I used checkout session instead of payment intent because it provides a pre-built checkout page that handles the entire payment flow, including collecting payment details and processing the payment, which simplifies the integration process. On the other hand, payment intent requires you to build your own custom checkout form and handle the payment flow manually, which can be more complex and time-consuming to implement.since I am building only backend API. 
        */
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
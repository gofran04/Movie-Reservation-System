<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Reservation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       return [
        'reservation_id' => Reservation::factory(),
        'amount'         => $this->faker->randomFloat(2, 10, 200),
        'status'         => 'succeeded',

        // Generic gateway reference
        'gateway_reference' => 'gw_' . Str::uuid(),

        // Refund reference initially null (only filled when refund is triggered)
        'refund_reference'  => null,

        // Stripe-specific field (nullable for multi-gateway design)
        'stripe_payment_intent_id' => 'pi_' . Str::random(24),
    ];
    }
}


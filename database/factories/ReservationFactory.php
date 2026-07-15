<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Showtime;
use App\Models\ReservationSeat;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'showtime_id' => Showtime::factory(),
            'status'      => 'pending',
            'total_price' => $this->faker->randomFloat(2, 10, 200),
            'expires_at'  => now()->addMinutes(10),
        ];
    }

    // create seats for the reservation after creating it, using existing seat ids
    public function withExistingSeats(array $seatIds): static
    {
        return $this->afterCreating(function ($reservation) use ($seatIds) {

            $rows = [];

            foreach ($seatIds as $seatId) {
                $rows[] = [
                    'reservation_id' => $reservation->id,
                    'seat_id'        => $seatId,
                    'showtime_id'    => $reservation->showtime_id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            ReservationSeat::insert($rows);
        });
    }

    // -------- STATUS --------

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status'     => 'pending',
            'expires_at' => now()->subMinute(10),
        ]);
    }
}

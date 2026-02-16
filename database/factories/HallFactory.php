<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cinema;
use App\Models\Hall;
use App\Services\CreateSeatService;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hall>
 */
class HallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => fake()->word(),
            'cinema_id'     => Cinema::first()->id,
            'total_rows'    => 1,
            'total_columns' => 2, // 2 seats in the hall
            'status'        => 'active',
        ];
    }

    // Creates seats for the hall
    public function configure()
    {
        return $this->afterCreating(function (Hall $hall) {
            CreateSeatService::createSeatsForHall($hall);
        });
    }
}

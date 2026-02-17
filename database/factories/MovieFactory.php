<?php

namespace Database\Factories;

use Doctrine\Inflector\Rules\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'                 => fake()->Word(),
            'description'           => fake()->paragraph(),
            'duration_minutes'      => 120,
            'release_year'          => fake()->year(),
            'rating'                => 8.4,
            'status'                => 'active'
        ];
    }
}

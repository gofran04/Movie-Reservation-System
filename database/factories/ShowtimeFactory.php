<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Showtime>
 */
class ShowtimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $movie = Movie::factory()->create();
        $hall = Hall::factory()->create();
        $startTime = Carbon::now()->addDays(rand(1, 30))->setTime(rand(10, 22), 0);

        return [
            'movie_id'      => $movie->id,
            'hall_id'       => $hall->id,
            'start_time'    => $startTime,
            'end_time'      => $startTime->copy()->addMinutes($movie->duration_minutes),
        ];
    }

    /**
     * Attach prices automatically after creation
     */
    public function configure()
    {
        return $this->afterCreating(function (Showtime $showtime) {
            $showtime->showtimePrices()->createMany([
                ['seat_type' => 'regular', 'price' => 30],
                ['seat_type' => 'vip',     'price' => 60],
            ]);
        });
    }

    // This method will created already started showtime with past start_time
    public function alreadyStarted()
    {
        return $this->state(function () {
            $movie = Movie::factory()->create();
            $hall  = Hall::factory()->create();

            $startTime = now()->subHours(4); // already started
            $endTime   = $startTime->copy()->addMinutes($movie->duration_minutes);

            return [
                'movie_id'   => $movie->id,
                'hall_id'    => $hall->id,
                'start_time' => $startTime,
                'end_time'   => $endTime,
            ];
        });
    }
}

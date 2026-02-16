<?php

namespace Tests\Feature;

use App\Models\Cinema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Illuminate\Support\Facades\Gate;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Hall;
use App\Models\User;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CinemaSeeder::class,
        ]);

        Gate::before(fn () => true); // Bypass authorization for testing purposes (Allow everything, skip authorization checks.to seedup the testing time)
    }

    public function test_auth_user_can_create_reservation(): void
    {
        // Act
        $user = User::factory()->create();
        $movie = Movie::factory()->create();
        $hall = Hall::factory()->create();
        $showtime = Showtime::factory()->create([
            'movie_id' => $movie->id,
            'hall_id'  => $hall->id,
        ]);

        $this->actingAs($user);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(2)->pluck('id')->toArray(),
        ];

        // Assert
        $response = $this->postJson('/api/reservations', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reservations', [
            'user_id'       => $user->id,
            'showtime_id'   => $showtime->id,
            'status'        => 'pending',
        ]);
    }
}

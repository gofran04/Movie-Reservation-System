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
        $showtime = $this->createShowtime();

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

    public function test_cannot_double_book_seat()
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user1);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(1)->pluck('id')->toArray(),
        ];

        // Act
        $response1 = $this->postJson('/api/reservations', $payload);
        $response1->assertStatus(201);

        // Attempt to book the same seat with another user
        $this->actingAs($user2);

        // Assert
        $response2 = $this->postJson('/api/reservations', $payload);
        $response2->assertStatus(422); // Expect validation error for double booking
        $response2->assertJsonValidationErrors(['seat_ids']); // Expect validation error for seat_ids

        // Ensure only one reservation exists for that seat
        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_reservation_is_atomic_and_rolls_back_on_failure()
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user1);

        $payload1 = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(1)->pluck('id')->toArray(), // Take seat #1. my hall has 2 seats only
        ];

        // Act
        $response1 = $this->postJson('/api/reservations', $payload1);
        $response1->assertStatus(201);

        $this->actingAs($user2);
        $payload2 = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(2)->pluck('id')->toArray(), // Attempt to take seat #1 and seat #2. my hall has 2 seats only
        ];

        // Act
        $response2 = $this->postJson('/api/reservations', $payload2);

        $response2->assertStatus(422); // Expect validation error for double booking
        $response2->assertJsonValidationErrors(['seat_ids']); // Expect validation error for seat_ids
        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseMissing('reservation_seats', [
            'seat_id' => $payload2['seat_ids'][1], // The second seat should be available and not reserved
        ]);
        $this->assertDatabaseHas('reservation_seats', [
            'seat_id' => $payload2['seat_ids'][0], // The first seat should be not available and reserved
        ]);
    }

    public function test_auth_user_can_cancel_reservation_and_seats_become_available()
    {
        // Arrange
        $user = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(1)->pluck('id')->toArray(),
        ];

        // Act
        $response = $this->postJson('/api/reservations', $payload);
        $response->assertStatus(201);

        $reservationId = $response->json('data.id');

        // Assert
        $cancelResponse = $this->postJson("/api/reservations/{$reservationId}/cancel");
        $cancelResponse->assertStatus(200);

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservationId,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseMissing('reservation_seats', [
            'seat_id' => $payload['seat_ids'][0], // The seat should be released and available again after cancellation
        ]);

        // Ensure the seat is now available for reservation again
        $newResponse = $this->postJson('/api/reservations', $payload);
        $newResponse->assertStatus(201);
        
    }

    private function createShowtime()
    {
        $movie = Movie::factory()->create();
        $hall = Hall::factory()->create();
        $showtime = Showtime::factory()->create([
            'movie_id' => $movie->id,
            'hall_id'  => $hall->id,
        ]);

        return $showtime;
    }
}


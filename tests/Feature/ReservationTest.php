<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;
use App\Jobs\CleanupExpiredReservationsJob;
use App\Models\Reservation;
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

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_reservation_with_future_expiration_is_not_expired(): void
    {
        Carbon::setTestNow('2026-08-16 12:00:00');
        $reservation = new Reservation(['expires_at' => now()->addMinute()]);

        $this->assertFalse($reservation->isExpired());
    }

    public function test_reservation_with_past_expiration_is_expired(): void
    {
        Carbon::setTestNow('2026-08-16 12:00:00');
        $reservation = new Reservation(['expires_at' => now()->subMinute()]);

        $this->assertTrue($reservation->isExpired());
    }

    public function test_reservation_at_expiration_boundary_is_expired(): void
    {
        Carbon::setTestNow('2026-08-16 12:00:00');
        $reservation = new Reservation(['expires_at' => now()]);

        $this->assertTrue($reservation->isExpired());
    }

    public function test_pending_non_expired_reservation_can_be_paid(): void
    {
        Carbon::setTestNow('2026-08-16 12:00:00');
        $reservation = new Reservation([
            'status' => 'pending',
            'expires_at' => now()->addMinute(),
        ]);

        $this->assertTrue($reservation->canBePaid());
    }

    public function test_created_reservation_expires_ten_minutes_after_creation(): void
    {
        Carbon::setTestNow('2026-08-16 12:00:00');
        $user = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'showtime_id' => $showtime->id,
            'seat_ids' => $showtime->hall->seats()->take(1)->pluck('id')->toArray(),
        ]);

        $response->assertCreated();

        $reservation = Reservation::findOrFail($response->json('data.id'));

        $this->assertTrue($reservation->expires_at->equalTo(now()->addMinutes(10)));
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
    
    public function test_user_can_not_reserve_zero_seats()
    {
        // Arrange
        $user = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => [], // No seats selected
        ];

        // Act
        $response = $this->postJson('/api/reservations', $payload);

        // Assert
        $response->assertStatus(422); // Expect validation error for no seats selected
        $response->assertJsonValidationErrors(['seat_ids']); // Expect validation error for seat_ids
    }

    public function test_user_can_not_reserve_not_existed_seats()
    {
        // Arrange
        $user = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => [999], // Non-existent seat ID
        ];

        // Act
        $response = $this->postJson('/api/reservations', $payload);

        // Assert
        $response->assertStatus(422); // Expect validation error for non-existent seat
        $this->assertArrayHasKey('seat_ids.0', $response->json('errors'));
    }

    public function test_user_can_not_reserve_showtime_in_the_past()
    {
        // Arrange
        $user = User::factory()->create();
        $movie = Movie::factory()->create();
        $hall = Hall::factory()->create();
        $showtime = Showtime::factory()->create([
            'movie_id' => $movie->id,
            'hall_id'  => $hall->id,
            'start_time' => now()->subHour(), // Showtime in the past
            'end_time' => now()->subHour()->addMinutes($movie->duration_minutes),
        ]);
        $this->actingAs($user);

        $seatIds = $showtime->hall->seats()->take(1)->pluck('id')->toArray();
        
        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $seatIds,
        ];

        $response = $this->postJson('/api/reservations', $payload);
        $response->assertStatus(422); // Expect validation error for showtime in the past
        $this->assertStringContainsString('You cannot reserve a showtime that has already started',
                $response->getContent());
    }

    public function test_user_can_not_book_seats_from_hall_other_than_showtime_hall()
    {
        // Arrange
        $user = User::factory()->create();
        $hall1 = Hall::factory()->create();
        $hall2 = Hall::factory()->create();
        $showtime1 = Showtime::factory()->create(['hall_id'  => $hall1->id]);
        
        $this->actingAs($user);

        $seatIds = $hall2->seats()->take(1)->pluck('id')->toArray();
        
        $payload = [
            'showtime_id' => $showtime1->id,
            'seat_ids'    => $seatIds,
        ];

        $response = $this->postJson('/api/reservations', $payload);

        $response->assertStatus(422); 
        $response->assertJsonValidationErrors('seat_ids');
    }

    public function test_expired_pending_reservations_are_cancelled_and_seats_released()
    {
        $showtime = $this->createShowtime();
        $seatIds = $showtime->hall->seats()->take(1)->pluck('id')->toArray();

        //create an expired reservation with the seat reserved
        $expiredReservation = Reservation::factory()
            ->expired()
            ->withExistingSeats($seatIds)
            ->create();

        //create a valid reservation with the seat reserved to ensure it is not affected by the cleanup job
        $validReservation = Reservation::factory()
            ->pending()
            ->withExistingSeats($showtime->hall->seats()->skip(1)->take(1)->pluck('id')->toArray())
            ->create();

        $job = new CleanupExpiredReservationsJob();
        $job->handle();// Run the job to cleanup expired reservations

        // Assert expired reservation was cancelled
        $this->assertDatabaseHas('reservations', [
            'id'     => $expiredReservation->id,
            'status' => 'cancelled',
        ]);

        // Assert seats were released
        $this->assertDatabaseMissing('reservation_seats', [
            'reservation_id' => $expiredReservation->id,
        ]);

        // Assert valid reservation untouched
        $this->assertDatabaseHas('reservations', [
            'id'     => $validReservation->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('reservation_seats', [
            'reservation_id' => $validReservation->id,
        ]);
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

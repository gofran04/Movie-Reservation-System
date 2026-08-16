<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\GeneralManagerSeeder;
use App\Models\Reservation;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Hall;
use App\Models\User;
use Illuminate\Support\Carbon;

class ReservationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CinemaSeeder::class,
            PermissionsSeeder::class,
            GeneralManagerSeeder::class,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_payment_is_denied_at_the_exact_reservation_expiration_time(): void
    {
        Carbon::setTestNow('2026-08-16 12:00:00');
        $user = User::factory()->create();
        $user->assignRole('client');
        $showtime = $this->createShowtime();
        $reservation = Reservation::factory()->pending()->create([
            'user_id' => $user->id,
            'showtime_id' => $showtime->id,
            'expires_at' => now(),
        ]);

        $this->actingAs($user);

        $this->postJson("/api/payments/{$reservation->id}")
            ->assertForbidden();
    }

    public function test_cancelling_same_reservation_twice_is_not_allowed()
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $showtime = $this->createShowtime();
        $seatIds = $showtime->hall->seats()->take(1)->pluck('id')->toArray();

        // Create a reservation
        $reservation = Reservation::factory()
            ->pending()
            ->withExistingSeats($seatIds)
            ->create([
                'user_id' => $user->id,
                'showtime_id' => $showtime->id,
            ]);

        $this->actingAs($user);

        // Cancel the reservation for the first time
        $response1 = $this->postJson("/api/reservations/{$reservation->id}/cancel");
        $response1->assertStatus(200);

        // Cancel the reservation for the second time
        $response2 = $this->postJson("/api/reservations/{$reservation->id}/cancel");
        $response2->assertStatus(403); // Should return 403 Forbidden (cause already canceleed reservations can not cancelled)

        // Assert reservation is cancelled and seats are released
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseMissing('reservation_seats', [
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_cannot_cancel_reservation_after_showtime_started()
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $showtime = Showtime::factory()->create([
            'start_time' => now()->subHour(), // Showtime started an hour ago
        ]);

        $seatIds = $showtime->hall->seats()->take(1)->pluck('id')->toArray();

        // Create a reservation
        $reservation = Reservation::factory()
            ->pending()
            ->withExistingSeats($seatIds)
            ->create([
                'user_id'     => $user->id,
                'showtime_id' => $showtime->id,
            ]);

        $this->actingAs($user);

        // Attempt to cancel the reservation
        $response = $this->postJson("/api/reservations/{$reservation->id}/cancel");
        $response->assertStatus(403); // Should return 403 Forbidden (cause showtime already started)
    
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'pending', // Reservation should still be pending
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

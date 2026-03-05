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
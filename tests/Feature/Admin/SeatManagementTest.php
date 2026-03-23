<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Database\Seeders\PermissionsSeeder;
use App\Models\Hall;
use App\Models\User;

class SeatManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CinemaSeeder::class,
            PermissionsSeeder::class,
         ]);
    }

    public function test_admins_can_view_all_seats_for_specific_hall(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $hall = Hall::factory()->create();
        $response = $this->getJson("/api/halls/{$hall->id}/seats");

        $response->assertStatus(200);
        $response->assertJsonCount(2); // Ensure that 2 seats are returned for the hall as per the HallFactory definition
        $this->assertDatabaseCount('seats', 2); // Ensure that 2 seats are created for the hall as per the HallFactory definition
    }

    public function test_admins_can_view_specific_seat(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $hall = Hall::factory()->create();
        $seatsId = $hall->seats()->first()->id;

        $response = $this->getJson("/api/seats/{$seatsId}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id'      => $seatsId,
                'hall_id' => $hall->id,
            ]
        ]);
        $this->assertDatabaseHas('seats', [
            'id' => $seatsId,
            'hall_id' => $hall->id
        ]);

    }

    public function test_admins_can_edit__specific_seat(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $hall = Hall::factory()->create();
        $seatsId = $hall->seats()->first()->id;

        $response = $this->putJson("/api/seats/{$seatsId}", [
            'status' => 'out_of_service',
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id'      => $seatsId,
                'hall_id' => $hall->id,
                'status'  => 'out_of_service',
            ]
        ]);
        $this->assertDatabaseHas('seats', [
            'id' => $seatsId,
            'hall_id' => $hall->id,
            'status'  => 'out_of_service',
        ]);
    }

    public function test_guests_cannot_manage_seats()
    {
        $hall = Hall::factory()->create();
        $seatsId = $hall->seats()->first()->id;

        // Guests cannot view seats
        $this->getJson("/api/halls/{$hall->id}/seats")->assertStatus(401);

        // Guests cannot view specific seat
        $this->getJson("/api/seats/{$seatsId}")->assertStatus(401);

        // Guests cannot edit specific seat
        $this->putJson("/api/seats/{$seatsId}", [
            'status' => 'out_of_service',
        ])->assertStatus(401);
    }

    public function test_unauthorized_users_cannot_manage_seats()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $hall = Hall::factory()->create();
        $seatsId = $hall->seats()->first()->id;

        // Unauthorized users cannot view seats
        $this->getJson("/api/halls/{$hall->id}/seats")->assertStatus(403);

        // Unauthorized users cannot view specific seat
        $this->getJson("/api/seats/{$seatsId}")->assertStatus(403);

        // Unauthorized users cannot edit specific seat
        $this->putJson("/api/seats/{$seatsId}", [
            'status' => 'out_of_service',
        ])->assertStatus(403);
    }
}

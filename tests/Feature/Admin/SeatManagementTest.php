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
}
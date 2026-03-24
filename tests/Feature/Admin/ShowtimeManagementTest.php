<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Database\Seeders\PermissionsSeeder;
use App\Models\Showtime;
use App\Models\User;

class ShowtimeManagementTest extends TestCase
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

    public function test__only_admins_can_view_all_showtimes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Showtime::factory()->count(3)->create();

        $response = $this->getJson('/api/showtimes');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $this->assertDatabaseCount('showtimes', 3);
    }


}

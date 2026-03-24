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

    public function test_only_admins_can_view_all_showtimes()
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

    public function test_only_admins_can_view_specific_showtime()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $showtime = Showtime::factory()->create();

        $response = $this->getJson("/api/showtimes/{$showtime->id}");

        $response->assertStatus(200);
        $response->assertJson([
                    'data' => [
                        'id'       => $showtime->id,
                        'movie_id' => $showtime->movie_id,
                        'hall_id'  => $showtime->hall_id,
                    ]
        ]);
        $this->assertDatabaseCount('showtimes', 1);
    }

}

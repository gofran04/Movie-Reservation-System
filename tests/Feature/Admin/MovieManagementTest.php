<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Database\Seeders\PermissionsSeeder;
use App\Models\Movie;
use App\Models\User;

class MovieManagementTest extends TestCase
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

    public function test_admins_can_view_all_movies()
     {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Movie::factory()->count(3)->create();

        $response = $this->getJson('/api/movies');
        
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $this->assertDatabaseCount('movies', 3);
    }
}
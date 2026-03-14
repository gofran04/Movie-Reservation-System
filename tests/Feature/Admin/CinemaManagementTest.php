<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\GeneralManagerSeeder;
use App\Models\Cinema;
use App\Models\User;

class CinemaManagementTest extends TestCase
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

    public function test_admins_can_view_cinema(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $cinema = Cinema::first();
        $response = $this->getJson("/api/cinemas/{$cinema->id}");

        $response->assertStatus(200);
        $response->assertJson([
               'data' => [
                   'id'          => $cinema->id,
                   'name'        => $cinema->name,
                   'location'    => $cinema->location,
               ]
        ]);
    }
}
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

    public function test_admins_can_edit_cinema(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $cinema = Cinema::first();
        $cinema->name = 'Updated Cinema Name';

        $response = $this->patch("/api/cinemas/{$cinema->id}",$cinema->toArray());

        $response->assertStatus(200);
        $response->assertJson([
               'data' => [
                   'id'          => $cinema->id,
                   'name'        => "Updated Cinema Name",
                   'location'    => $cinema->location,
               ]
        ]);
    }

    public function test_unauthorized_users_cannot_manage_cinema(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cinema = Cinema::first();
        $cinema->name = 'Updated Cinema Name';

        $this->patch("/api/cinemas/{$cinema->id}",$cinema->toArray())->assertForbidden();
    }

    public function test_guests_cannot_manage_cinema(): void
    {
        $cinema = Cinema::first();
        $cinema->name = 'Updated Cinema Name';

        $this->patchJson("/api/cinemas/{$cinema->id}",$cinema->toArray())->assertStatus(401);// Unauthenticated user
    }
}
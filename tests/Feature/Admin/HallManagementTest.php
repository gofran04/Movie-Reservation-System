<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Database\Seeders\PermissionsSeeder;
use App\Models\Hall;
use App\Models\User;

class HallManagementTest extends TestCase
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

    public function test__only_admins_can_view_all_halls()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Hall::factory()->count(3)->create();

        $response = $this->getJson('/api/halls');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $this->assertDatabaseCount('halls', 3);
    }

    public function test_only_admins_can_view_specific_hall()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $hall = Hall::factory()->create();

        $response = $this->getJson("/api/halls/{$hall->id}");

        $response->assertStatus(200);
        $response->assertJson([
                    'data' => [
                        'id'         => $hall->id,
                        'name'       => $hall->name,
                        'cinema_id'  => $hall->cinema_id,
                    ]
        ]);
        $this->assertDatabaseCount('halls', 1);
    }

    public function test_only_admins_can_create_a_hall()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $data = Hall::factory()->make(['name' => 'Test Hall'])->toArray();

        $response = $this->postJson("/api/halls",$data);

        $response->assertStatus(201);
        $response->assertJson([
                    'data' => [
                        'name'       => $data['name'],
                        'cinema_id'  => $data['cinema_id'],
                    ]
        ]);
        $this->assertDatabaseHas('halls', ['name' => $data['name']]);
        $this->assertDatabaseCount('halls', 1);
    }

    public function test_only_admins_can_edit_a_hall()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $hall = Hall::factory()->create();
        $hall->name = 'Updated Hall Name';
        $hall->status = 'inactive';

        // Note: We only send the fields that are allowed to be updated according to UpdateHallRequest (name and status). cinema_id, total_rows, and total_columns are prohibited from being updated.
        $response = $this->putJson("/api/halls/{$hall->id}", [
            'name' => $hall->name,
            'status' => $hall->status
        ]);

        $response->assertStatus(200);
        $response->assertJson([
                    'data' => [
                        'id'     => $hall->id,
                        'name'   => 'Updated Hall Name',
                        'status' => 'inactive'
                    ]
        ]);
        $this->assertDatabaseHas('halls', [
            'id'    => $hall->id,
            'name' => 'Updated Hall Name'
        ]);
    }

    public function test_guests_cannot_manage_halls()
    {
        $hall = Hall::factory()->create();
        $hall->name = 'Updated Hall Name';
        $hall->status = 'inactive';

        $this->getJson('/api/halls')->assertStatus(401); //index - Unauthenticated user
        $this->getJson("/api/halls/{$hall->id}")->assertStatus(401); //show - Unauthenticated user
        $this->postJson('/api/halls', $hall->toArray())->assertStatus(401); //store - Unauthenticated user
        $this->patchJson("/api/halls/{$hall->id}",$hall->toArray())->assertStatus(401);//update - Unauthenticated user
    }

    public function test_unauthorized_users_cannot_manage_movies()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $hall = Hall::factory()->create();
        $hall->name = 'Updated Hall Name';
        $hall->status = 'inactive';
        
        $this->getJson('/api/halls')->assertForbidden(); //index - Unauthorized user
        $this->getJson("/api/halls/{$hall->id}")->assertForbidden(); //show - Unauthorized user
        $this->postJson('/api/halls', $hall->toArray())->assertForbidden(); //store - Unauthorized user
        $this->patchJson("/api/halls/{$hall->id}",[
            'name' => $hall->name,
            'status' => $hall->status
        ])->assertForbidden();//update - Unauthorized user
    }
}
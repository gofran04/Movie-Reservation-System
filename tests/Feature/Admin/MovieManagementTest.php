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

    public function test__only_admins_can_view_all_movies()
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

    public function test_only_admins_can_view_specific_movie()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $movie = Movie::factory()->create();

        $response = $this->getJson("/api/movies/{$movie->id}");

        $response->assertStatus(200);
        $response->assertJson([
                    'data' => [
                        'id'          => $movie->id,
                        'title'       => $movie->title,
                        'description' => $movie->description,
                    ]
        ]);
        $this->assertDatabaseCount('movies', 1);
    }

    public function test_only_admins_can_create_a_movie()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $data = Movie::factory()->make(['title' => 'Test Movie'])->toArray();

        $response = $this->postJson("/api/movies",$data);

        $response->assertStatus(201);
        $response->assertJson([
                    'data' => [
                        'title'  => $data['title'],
                    ]
        ]);
        $this->assertDatabaseHas('movies', ['title'  => $data['title']]);
        $this->assertDatabaseCount('movies', 1);
    }

    public function test_only_admins_can_edit_a_movie()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $movie = Movie::factory()->create();
        $movie->title = 'Updated Movie Title';

        $response = $this->putJson("/api/movies/{$movie->id}", $movie->toArray());

        $response->assertStatus(200);
        $response->assertJson([
                    'data' => [
                        'id'    => $movie->id,
                        'title' => 'Updated Movie Title',
                    ]
        ]);
        $this->assertDatabaseHas('movies', [
            'id'    => $movie->id,
            'title' => 'Updated Movie Title'
        ]);
    }

    public function test_only_admins_can_delete_a_movie()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $movie = Movie::factory()->create();

        $response = $this->deleteJson("/api/movies/{$movie->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($movie);
        $this->assertEquals(0, Movie::count());
    }

    public function test_guests_cannot_manage_movies()
    {
        $movie = Movie::factory()->create();
        $movie->title = 'Updated Movie Title';

        $this->postJson('/api/movies', $movie->toArray())->assertStatus(401); //store - Unauthenticated user
        $this->patchJson("/api/movies/{$movie->id}",$movie->toArray())->assertStatus(401);//update - Unauthenticated user
        $this->deleteJson("/api/movies/{$movie->id}")->assertStatus(401);//destroy - Unauthenticated user
    }

    public function test_unauthorized_users_cannot_manage_movies()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $movie = Movie::factory()->create();
        $movie->title = 'Updated Movie Title';

        $this->postJson('/api/movies', $movie->toArray())->assertForbidden(); //store - Unauthorized user
        $this->patchJson("/api/movies/{$movie->id}",$movie->toArray())->assertForbidden();//update - Unauthorized user
        $this->deleteJson("/api/movies/{$movie->id}")->assertForbidden();//destroy - Unauthorized user
    }
}
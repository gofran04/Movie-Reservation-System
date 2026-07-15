<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Database\Seeders\PermissionsSeeder;
use App\Models\Reservation;
use App\Models\Showtime;
use App\Models\User;
use Carbon\Carbon;

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

    public function test_only_admins_can_create_a_showtime()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $data = Showtime::factory()->make()->toArray();
        $data['start_time'] = Carbon::parse($data['start_time'])->format('Y-m-d H:i:s');
        $data['end_time']   = Carbon::parse($data['end_time'])->format('Y-m-d H:i:s');

        $data['prices'] = [
            'regular' => 30,
            'vip'     => 60,
        ];

        $response = $this->postJson("/api/showtimes",$data);

        $response->assertStatus(201);
        $response->assertJson([
                    'data' => [
                        'movie_id'   => $data['movie_id'],
                        'hall_id'    => $data['hall_id'],
                        'start_time' => $data['start_time'],
                        'end_time'   => $data['end_time'],
                    ]
        ]);
        $this->assertDatabaseHas('showtimes', ['movie_id' => $data['movie_id'],'start_time' => $data['start_time']]);
        $this->assertDatabaseCount('showtimes', 1);
    }

    public function test_only_admins_can_edit_a_showtime()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $showtime = Showtime::factory()->create();
        $showtime->start_time = Carbon::parse($showtime->start_time)->addDays(1)->format('Y-m-d H:i:s');

        $response = $this->putJson("/api/showtimes/{$showtime->id}", $showtime->toArray());
        $response->assertStatus(200);
        $response->assertJson([
                    'data' => [
                        'id'         => $showtime->id,
                        'start_time' => $showtime->start_time,
                        'end_time'   => Carbon::parse($showtime->start_time)->addMinutes($showtime->movie->duration_minutes)->format('Y-m-d H:i:s'),
                    ]
        ]);
        $this->assertDatabaseHas('showtimes', [
            'id'         => $showtime->id,
            'start_time' => $showtime->start_time
            ]);
    }

    public function test_only_admins_can_delete_not_started_showtime()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $showtime = Showtime::factory()->create();

        $response = $this->deleteJson("/api/showtimes/{$showtime->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($showtime);
        $this->assertEquals(0, Showtime::count());
    }

    public function test_only_admins_can_not_delete_already_started_showtime()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $showtime = Showtime::factory()->alreadyStarted()->create();
        $response = $this->deleteJson("/api/showtimes/{$showtime->id}");

        $response->assertStatus(403);
        $this->assertNotSoftDeleted($showtime);
        $this->assertEquals(1, Showtime::count());
    }

    public function test_only_admins_can_not_delete_reserved_showtime()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $showtime = Showtime::factory()->create();
        Reservation::factory()->confirmed()->create([
            'showtime_id' => $showtime->id,
        ]);

        $response = $this->deleteJson("/api/showtimes/{$showtime->id}");

        $response->assertStatus(403);
        $this->assertNotSoftDeleted($showtime);
        $this->assertEquals(1, Showtime::count());
    }

    public function test_guests_cannot_manage_showtimes()
    {
        $showtime = Showtime::factory()->create();
        $showtime->start_time = Carbon::parse($showtime->start_time)->addDays(1)->format('Y-m-d H:i:s');

        $this->postJson('/api/showtimes', $showtime->toArray())->assertStatus(401); //store - Unauthenticated user
        $this->patchJson("/api/showtimes/{$showtime->id}",$showtime->toArray())->assertStatus(401);//update - Unauthenticated user
        $this->deleteJson("/api/showtimes/{$showtime->id}")->assertStatus(401);//destroy - Unauthenticated user
    }

    public function test_unauthorized_users_cannot_manage_showtimes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = Showtime::factory()->make()->toArray();
        $data['start_time'] = Carbon::parse($data['start_time'])->format('Y-m-d H:i:s');
        $data['end_time']   = Carbon::parse($data['end_time'])->format('Y-m-d H:i:s');

        $data['prices'] = [
            'regular' => 30,
            'vip'     => 60,
        ];

        $showtime = Showtime::factory()->create();
        $showtime->start_time = Carbon::parse($showtime->start_time)->addDays(1)->format('Y-m-d H:i:s');

        $this->postJson('/api/showtimes', $data)->assertForbidden(); //store - Unauthorized user
        $this->patchJson("/api/showtimes/{$showtime->id}",$showtime->toArray())->assertForbidden();//update - Unauthorized user
        $this->deleteJson("/api/showtimes/{$showtime->id}")->assertForbidden();//destroy - Unauthorized user
    }
}

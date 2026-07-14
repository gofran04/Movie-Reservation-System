<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'edit-profile',
            'view-profile',

            'create-role',
            'edit-role',
            'view-role',
            'view-all-roles',
            'delete-role',

            'create-user',
            'edit-user',
            'view-user',
            'view-all-users',
            'delete-user',

            'suspend-user',
            'activate-user',

            'edit-cinema',

            'create-hall',
            'edit-hall',
            'view-hall',
            'view-all-halls',
            'delete-hall',

            'view-all-seats',
            'view-seat',
            'edit-seat',
            'delete-seat',

            'view-all-movies',
            'view-movie',
            'create-movie',
            'edit-movie',
            'delete-movie',

            'create-showtime',
            'edit-showtime',
            'delete-showtime',

            'create-reservation',
            'view-reservation',
            'view-all-reservations',
            'cancel-any-reservation',
            'cancel-own-reservation',
            'pay-any-reservation',
            'pay-own-reservation',
        ];

        foreach ($permissions as $permission) 
        {
            // idomptent seeding to avoid duplicates
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // idomptent seeding to avoid duplicates
       $client = Role::firstOrCreate(['guard_name' => 'sanctum','name' => 'client']);
       $client->syncPermissions([
            'edit-profile',
            'view-profile',

            'create-reservation',
            'view-reservation',
            'cancel-own-reservation',
            'pay-own-reservation',
       ]);

        // idomptent seeding to avoid duplicates
       $supervisor =  Role::firstOrCreate(['guard_name' => 'sanctum','name' => 'admin']);
       $supervisor->syncPermissions([
            'edit-user',
            'view-user',
            'view-all-users',

            'edit-cinema',

            'create-hall',
            'edit-hall',
            'view-hall',
            'view-all-halls',
            'delete-hall',

            'view-all-seats',
            'view-seat',
            'edit-seat',
            'delete-seat',

            'view-all-movies',
            'view-movie',
            'create-movie',
            'edit-movie',
            'delete-movie',

            'create-showtime',
            'edit-showtime',
            'delete-showtime',

            'view-reservation',
            'view-all-reservations',
            'cancel-any-reservation',
            'pay-any-reservation',
         ]);
        }
}

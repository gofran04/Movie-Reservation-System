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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
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

            'view-cinema',
            'edit-cinema',

            'create-hall',
            'edit-hall',
            'view-hall',
            'view-all-halls',
            'delete-hall',

            'view-all-seats',
            'view-seat',
            'create-seat',
            'edit-seat',
            'delete-seat',

            'view-all-movies',
            'view-movie',
            'create-movie',
            'edit-movie',
            'delete-movie',

            'view-all-showtimes',
            'view-showtime',
            'create-showtime',
            'edit-showtime',
            'delete-showtime',

            'create-reservation',
            'edit-reservation',
            'view-reservation',
            'view-all-reservations',
            'delete-reservation',
        ];

        foreach ($permissions as $permission) 
        {
            Permission::create(['name' => $permission]);
        }

       $client = Role::create(['guard_name' => 'api','name' => 'client']);
       $client->syncPermissions([
            'edit-profile',
            'view-profile',

            'create-reservation',
            'edit-reservation',
            'view-reservation',
            'view-all-reservations',
       ]);

       $supervisor =  Role::create(['guard_name' => 'api','name' => 'admin']);
       $supervisor->syncPermissions([
            'edit-user',
            'view-user',
            'view-all-users',

            'view-cinema',
            'edit-cinema',

            'create-hall',
            'edit-hall',
            'view-hall',
            'view-all-halls',
            'delete-hall',

            'view-all-seats',
            'view-seat',
            'create-seat',
            'edit-seat',
            'delete-seat',

            'view-all-movies',
            'view-movie',
            'create-movie',
            'edit-movie',
            'delete-movie',

            'view-all-showtimes',
            'view-showtime',
            'create-showtime',
            'edit-showtime',
            'delete-showtime',

            'edit-reservation',
            'view-reservation',
            'view-all-reservations',
         ]);
    }
}

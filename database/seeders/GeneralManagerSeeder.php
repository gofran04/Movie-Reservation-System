<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class GeneralManagerSeeder extends Seeder
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

        $general_manager_attributes = [
            'name'              => "TheGeneralManager",
            'phone'             => '011111111',
            'email'             => "GeneralManager@mail.com",
            'password'          => Hash::make('admin123'),
            'role'              => 'admin',

        ];

        $generalManagerUser = User::create($general_manager_attributes);
        $generalManagerRole = Role::create(['guard_name' => 'sanctum','name' => 'General-Manager']);
        $permissions = Permission::where('guard_name', 'sanctum')->get();
        $generalManagerRole->syncPermissions($permissions);        
        $generalManagerUser->assignRole($generalManagerRole);

    }
}

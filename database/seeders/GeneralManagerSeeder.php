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
        

        //idomptent seeding to avoid duplicates
        $generalManagerUser = User::firstOrCreate(
            ['email' => 'GeneralManager@mail.com'], // unique identifier for the user
            [
                'name' => 'TheGeneralManager',
                'phone' => '011111111',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );
        $generalManagerRole = Role::firstOrCreate(['guard_name' => 'sanctum','name' => 'General-Manager']);
        
        $permissions = Permission::where('guard_name', 'sanctum')->get();

        $generalManagerRole->syncPermissions($permissions);  

        if (!$generalManagerUser->hasRole($generalManagerRole)) {
            $generalManagerUser->assignRole($generalManagerRole);
        }      
    }
}

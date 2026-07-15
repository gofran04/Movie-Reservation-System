<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cinema;

class CinemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cinema_attributes = [
            'name'            => 'Grand Cinema',
            'location'        => 'Downtown',
        ];

        //idomptent seeding to avoid duplicates
        Cinema::firstOrCreate($cinema_attributes);
    }
}

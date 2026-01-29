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

        Cinema::create($cinema_attributes);
    }
}

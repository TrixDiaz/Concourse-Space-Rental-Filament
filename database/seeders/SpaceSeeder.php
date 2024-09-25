<?php

namespace Database\Seeders;

use App\Models\Concourse;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Space::create([
            'user_id' => User::select('id')->inRandomOrder()->first()->id,
            'concourse_id' => Concourse::select('id')->inRandomOrder()->first()->id,
            'name' => 'Space 1',
            'price' => rand(1000, 10000),
            'status' => 'available',
            'sqm' => rand(10, 100),
            'is_active' => true,
            'space_width' => rand(10, 100),
            'space_length' => rand(10, 100),
            'space_area' => rand(10, 100),
            'space_dimension' => rand(10, 100),
            'space_coordinates_x' => rand(10, 100),
            'space_coordinates_y' => rand(10, 100),
            'space_coordinates_x2' => rand(10, 100),
            'space_coordinates_y2' => rand(10, 100),
        ]);
    }
}

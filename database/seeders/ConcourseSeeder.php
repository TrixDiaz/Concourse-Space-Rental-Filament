<?php

namespace Database\Seeders;

use App\Models\Concourse;
use App\Models\ConcourseRate;
use App\Models\Space;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConcourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Concourse::create([
            'rate_id' => ConcourseRate::select('id')->inRandomOrder()->first()->id,
            'name' => 'PUP - Main Concourse',
            'address' => 'General Luna St. , Sampaloc, Manila, 1003 Metro Manila',
            'spaces' => Space::select('concourse_id')->count(),
            'image' => 'https://placehold.co/600x400',
            'layout' => 'https://placehold.co/600x400',
            'lease_term' => rand(1, 10),
            'water_bills' => rand(1000, 10000),
            'electricity_bills' => rand(1000, 10000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

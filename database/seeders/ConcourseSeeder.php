<?php

namespace Database\Seeders;

use App\Models\Concourse;
use App\Models\ConcourseRate;
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
            'spaces' => 100,
            'image' => 'https://placehold.co/600x400',
            'layout' => 'https://placehold.co/600x400',
            'lease_term' => rand(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

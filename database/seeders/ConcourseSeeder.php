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
            'name' => 'Concourse 1',
            'address' => 'Concourse 1 description',
            'spaces' => 100,
            'image' => 'https://placehold.co/600x400',
            'layout' => 'https://placehold.co/600x400',
            'lease_term' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

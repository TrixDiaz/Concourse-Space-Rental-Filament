<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Concourse;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Application::create([
            'user_id' => User::select('id')->inRandomOrder()->first()->id,
            'concourse_id' => Concourse::select('id')->inRandomOrder()->first()->id,
            'space_id' => Space::select('id')->inRandomOrder()->first()->id,
            'status' => 'available',
            'business_name' => 'Business 1',
            'owner_name' => 'Owner 1',
            'email' => 'owner1@gmail.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Raya No. 1',
            'business_type' => 'Restaurant',
            'expiration_date' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

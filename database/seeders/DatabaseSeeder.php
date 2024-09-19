<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Concourse;
use App\Models\ConcourseRate;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Series;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Concourse::factory(10)->create();
        
      
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'birth_date' => now(),
        ]);

        ConcourseRate::create(
            [
                'name' => 'City',
                'price' => 2000,
                'is_active' => true,
            ],
            [
                'name' => 'Province',
                'price' => 1000,
                'is_active' => true,
            ]);
       
    }
}

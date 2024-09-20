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
use App\Models\Space;
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
        ConcourseRate::factory(1)->create();
        Concourse::factory(10)->create();
        Space::factory(10)->create();
        $this->call(RequirementSeeder::class);
      
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'birth_date' => now(),
        ]);
    }
}

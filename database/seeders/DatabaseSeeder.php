<?php

namespace Database\Seeders;


use App\Models\Application;
use App\Models\Concourse;
use App\Models\ConcourseRate;
use App\Models\User;
use App\Models\Space;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\Requirement;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(2)->create();
        ConcourseRate::factory(2)->create();
        Concourse::factory(2)->create();
        Space::factory(2)->create();
        Application::factory(2)->create();
        Tenant::factory(2)->create();
        $this->call(RequirementSeeder::class);
        Payment::factory(2)->create();
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'birth_date' => now(),
        ]);
    }
}

<?php

namespace Database\Seeders;


use App\Models\Application;
use App\Models\Concourse;
use App\Models\ConcourseRate;
use App\Models\User;
use App\Models\Space;
use App\Models\Tenant;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(ConcourseRateSeeder::class);
        Concourse::factory(2)->create();
        Space::factory(2)->create();
        Application::factory(2)->create();
        Tenant::factory(2)->create();
        $this->call(RequirementSeeder::class);
        Payment::factory(2)->create();
    }
}

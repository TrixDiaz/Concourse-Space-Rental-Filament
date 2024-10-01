<?php

namespace Database\Seeders;

use App\Models\Concourse;
use App\Models\Space;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::create([
            'tenant_id' => User::select('id')->inRandomOrder()->first()->id,
            'concourse_id' => Concourse::select('id')->inRandomOrder()->first()->id,
            'space_id' => Space::select('id')->inRandomOrder()->first()->id,
            'owner_id' => User::select('id')->inRandomOrder()->first()->id,
            'lease_start' => now(),
            'lease_end' => now()->addYear(),
            'lease_term' => 1,
            'lease_status' => 'active',
            'lease_due' => now()->addMonth(),
            'payment_status' => 'paid',
            'monthly_payment' => 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

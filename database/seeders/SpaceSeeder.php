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
        for ($i = 1; $i <= 3; $i++) {
            Space::create(
                [
                    'concourse_id' => Concourse::select('id')->inRandomOrder()->first()->id,
                    'name' => "Space $i",
                    'price' => rand(1000, 10000),
                    'status' => 'occupied',
                    'sqm' => rand(10, 100),
                    'business_name' => 'Business Name',
                    'owner_name' => 'Owner Name',
                    'address' => 'Address',
                    'phone_number' => '1234567890',
                    'business_type' => 'Business Type',
                    'water_consumption' => null,
                    'electricity_consumption' => null,
                    'water_bills' => null,
                    'electricity_bills' => null,
                    'rent_bills' => null,
                    'water_payment_status' => null,
                    'electricity_payment_status' => null,
                    'rent_payment_status' => null,
                    'lease_start' => now(),
                    'lease_due' => now()->addMonths(rand(1, 12)),
                    'lease_end' => now()->addYears(rand(1, 10)),
                    'lease_term' => rand(1, 10),
                    'lease_status' => 'active',
                    'is_active' => true,
                    'space_width' => rand(10, 100),
                    'space_length' => rand(10, 100),
                    'space_area' => rand(10, 100),
                    'space_dimension' => rand(10, 100),
                    'space_coordinates_x' => rand(10, 100),
                    'space_coordinates_y' => rand(10, 100),
                    'space_coordinates_x2' => rand(10, 100),
                    'space_coordinates_y2' => rand(10, 100),
                ]
            );
        }
    }
}

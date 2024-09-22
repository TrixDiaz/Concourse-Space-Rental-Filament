<?php

namespace Database\Factories;

use App\Models\Concourse;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => User::factory(),
            'concourse_id' => Concourse::factory(),
            'space_id' => Space::factory(),
            'owner_id' => User::factory(),
            'lease_start' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'lease_end' => $this->faker->dateTimeBetween('now', '+1 year'),
            'lease_term' => $this->faker->numberBetween(1, 12),
            'lease_status' => $this->faker->randomElement(['active', 'inactive', 'expired']),
            'is_active' => $this->faker->boolean,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Concourse;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'concourse_id' => Concourse::factory(),
            'space_id' => Space::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'business_name' => $this->faker->name,
            'owner_name' => $this->faker->name,
            'email' => $this->faker->email,
            'phone_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address,
            'business_type' => $this->faker->randomElement(['restaurant', 'retail', 'other']),
            'expiration_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

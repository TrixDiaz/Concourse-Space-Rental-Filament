<?php

namespace Database\Factories;

use App\Models\Concourse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'concourse_id' => Concourse::factory()->create()->id,
            'name' => $this->faker->name,
            'price' => $this->faker->numberBetween(1000, 10000),
            'status' => $this->faker->randomElement(['available', 'occupied', 'reserved']),
            'is_active' => $this->faker->boolean,
            'space_width' => $this->faker->numberBetween(10, 100),
            'space_length' => $this->faker->numberBetween(10, 100),
            'space_area' => $this->faker->numberBetween(10, 100),
            'space_dimension' => $this->faker->randomElement(['10x10', '20x20', '30x30']),
            'space_coordinates_x' => $this->faker->numberBetween(10, 100),
            'space_coordinates_y' => $this->faker->numberBetween(10, 100),
            'space_coordinates_x2' => $this->faker->numberBetween(10, 100),
            'space_coordinates_y2' => $this->faker->numberBetween(10, 100),
            'lease_term' => $this->faker->date, 
        ];
    }
}

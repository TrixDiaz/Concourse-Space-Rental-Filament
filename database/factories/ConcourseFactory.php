<?php

namespace Database\Factories;

use App\Models\ConcourseRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Concourse>
 */
class ConcourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'address' => fake()->address(),
            'rate_id' => ConcourseRate::factory()->create()->id,
            'lease_term' => fake()->date(),
            'spaces' => fake()->numberBetween(0, 20),
            'is_active' => fake()->boolean(),
        ];
    }
}

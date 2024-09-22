<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'payment_type' => $this->faker->randomElement(['Rent', 'Utility', 'Other']),
            'payment_method' => $this->faker->randomElement(['Cash', 'Bank Transfer', 'Online Payment']),
            'payment_status' => $this->faker->randomElement(['Pending', 'Paid', 'Late']),
        ];
    }
}

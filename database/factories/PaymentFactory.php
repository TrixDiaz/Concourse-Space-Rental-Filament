<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
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
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();

        $paymentDetails = [
            [
                'name' => 'water',
                'amount' => (string) $this->faker->numberBetween(50, 500)
            ],
            [
                'name' => 'electricity',
                'amount' => (string) $this->faker->numberBetween(50, 500)
            ]
        ];

        return [
            'tenant_id' => $this->faker->randomElement(User::pluck('id')),
            'amount' => collect($paymentDetails)->sum(fn($item) => (int) $item['amount']),
            'payment_details' => json_encode($paymentDetails),
            'payment_method' => $this->faker->randomElement(['maya', 'gcash']),
            'payment_status' => $this->faker->randomElement(['paid', 'unpaid', 'overdue', 'pending']),
            'payment_type' => $this->faker->randomElement(['cash', 'e-wallet']),
            'created_at' => $this->faker->dateTimeBetween($startDate, $endDate),
        ];
    }
}

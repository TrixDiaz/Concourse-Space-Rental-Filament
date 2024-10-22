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

        $waterBill = $this->faker->numberBetween(50, 500);
        $electricityBill = $this->faker->numberBetween(50, 500);

        return [
            'tenant_id' => $this->faker->randomElement(User::pluck('id')),
            'space_id' => $this->faker->randomElement(Space::pluck('id')),
            'amount' => $waterBill + $electricityBill,
            'payment_type' => $this->faker->randomElement(['cash', 'e-wallet']),
            'payment_method' => $this->faker->randomElement(['cash', 'gcash']),
            'payment_status' => $this->faker->randomElement(['paid', 'unpaid']),
            'water_bill' => $waterBill,
            'electricity_bill' => $electricityBill,
            'water_consumption' => $this->faker->numberBetween(1, 100) . ' cubic meters',
            'electricity_consumption' => $this->faker->numberBetween(50, 500) . ' kWh',
            'rent_bill' => $this->faker->numberBetween(1000, 5000),
            'created_at' => $this->faker->dateTimeBetween($startDate, $endDate),
        ];
    }

    /**
     * Configure the factory to generate 500 records with numeric tenant IDs.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->count(500)->state(function (array $attributes) {
            return [
                'tenant_id' => User::find(2)->id,
            ];
        });
    }
}

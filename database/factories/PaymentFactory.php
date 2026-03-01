<?php

namespace Database\Factories;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
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
            'loan_id' => Loan::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'payment_method' => $this->faker->randomElement(['cash', 'gcash', 'card']),
            'payment_date' => $this->faker->date(),
            'reference_number' => $this->faker->uuid(),
            'notes' => $this->faker->sentence(),
        ];
    }
}

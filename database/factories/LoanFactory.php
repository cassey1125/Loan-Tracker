<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\Borrower;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $borrower = Borrower::inRandomOrder()->first() ?? Borrower::factory()->create();
        $amount = $this->faker->randomFloat(2, 1000, 50000);
        $interestRate = $this->faker->randomElement([5, 7, 10]);
        $paymentTerm = $this->faker->numberBetween(1, 12);
        
        // Interest = Principal * (Rate / 100) * Term
        $interestAmount = $amount * ($interestRate / 100) * $paymentTerm;
        $totalPayable = $amount + $interestAmount;
        
        return [
            'borrower_id' => $borrower->id,
            'amount' => $amount,
            'interest_rate' => $interestRate,
            'payment_term' => $paymentTerm,
            'interest_amount' => $interestAmount,
            'total_payable' => $totalPayable,
            'remaining_balance' => $totalPayable,
            'due_date' => now()->addMonths($paymentTerm),
            'status' => LoanStatus::PENDING,
        ];
    }
}

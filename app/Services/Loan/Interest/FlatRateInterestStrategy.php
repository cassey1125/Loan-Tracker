<?php

namespace App\Services\Loan\Interest;

class FlatRateInterestStrategy implements InterestCalculationStrategy
{
    public function calculate(float $principal, float $rate, int $term): float
    {
        // Simple calculation: Principal * (Rate / 100)
        // This assumes the rate is applied flat on the principal regardless of the term duration unit for simplicity,
        // unless specific term logic is requested.
        return $principal * ($rate / 100);
    }
}

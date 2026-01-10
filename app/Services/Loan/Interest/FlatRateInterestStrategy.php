<?php

namespace App\Services\Loan\Interest;

class FlatRateInterestStrategy implements InterestCalculationStrategy
{
    public function calculate(float $principal, float $rate, int $term): float
    {
        // Interest = Principal * (Rate / 100) * Term
        return $principal * ($rate / 100) * $term;
    }
}

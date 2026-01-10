<?php

namespace App\Services\Loan\Interest;

interface InterestCalculationStrategy
{
    public function calculate(float $principal, float $rate, int $term): float;
}

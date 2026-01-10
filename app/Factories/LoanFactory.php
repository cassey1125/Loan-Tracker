<?php

namespace App\Factories;

use App\Models\Loan;

class LoanFactory
{
    public function create(array $data): Loan
    {
        return new Loan($data);
    }
}

<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientFundsException extends RuntimeException
{
    public function __construct(float $availableFunds)
    {
        parent::__construct('Loan amount is greater than available funds. Available funds: '.number_format($availableFunds, 2).'. Please add funds before creating this loan.');
    }
}

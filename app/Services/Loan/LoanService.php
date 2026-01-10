<?php

namespace App\Services\Loan;

use App\Enums\LoanStatus;
use App\Factories\LoanFactory;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use App\Services\Loan\Interest\InterestCalculationStrategy;

class LoanService
{
    public function __construct(
        protected LoanRepository $repository,
        protected LoanFactory $factory,
        protected InterestCalculationStrategy $interestStrategy
    ) {}

    public function createLoan(array $data): Loan
    {
        $amount = (float) $data['amount'];
        $rate = (float) $data['interest_rate'];
        $term = (int) $data['payment_term'];

        $interest = $this->interestStrategy->calculate($amount, $rate, $term);
        $totalPayable = $amount + $interest;

        $loanData = array_merge($data, [
            'interest_amount' => $interest,
            'total_payable' => $totalPayable,
            'remaining_balance' => $totalPayable,
            'status' => LoanStatus::PENDING,
        ]);

        $loan = $this->factory->create($loanData);

        return $this->repository->save($loan);
    }
}

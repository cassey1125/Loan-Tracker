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

    public function updateLoan(Loan $loan, array $data): Loan
    {
        $amount = (float) $data['amount'];
        $rate = (float) $data['interest_rate'];
        $term = (int) $data['payment_term'];

        // Recalculate financial details
        $interest = $this->interestStrategy->calculate($amount, $rate, $term);
        $totalPayable = $amount + $interest;
        
        // Calculate how much has been paid so far
        $paidAmount = $loan->total_payable - $loan->remaining_balance;
        
        // New remaining balance
        $remainingBalance = $totalPayable - $paidAmount;
        
        // Prevent negative balance if new total is less than paid amount (optional safety)
        if ($remainingBalance < 0) {
            $remainingBalance = 0; 
            // Ideally we might want to warn or handle this, but for now we clamp to 0
        }

        $loan->fill(array_merge($data, [
            'interest_amount' => $interest,
            'total_payable' => $totalPayable,
            'remaining_balance' => $remainingBalance,
            // We might want to re-evaluate status based on new balance
            'status' => $remainingBalance <= 0 ? LoanStatus::PAID : ($loan->status === LoanStatus::PAID ? LoanStatus::PENDING : $loan->status),
        ]));

        return $this->repository->save($loan);
    }
}

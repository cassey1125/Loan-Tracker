<?php

namespace App\Services\Loan;

use App\Exceptions\InsufficientFundsException;
use App\Enums\LoanStatus;
use App\Factories\LoanFactory;
use App\Models\Fund;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use App\Services\Loan\Interest\InterestCalculationStrategy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        protected LoanRepository $repository,
        protected LoanFactory $factory,
        protected InterestCalculationStrategy $interestStrategy,
        protected LoanInstallmentService $installmentService
    ) {}

    public function createLoan(array $data): Loan
    {
        return DB::transaction(function () use ($data): Loan {
            $amount = (float) $data['amount'];
            $this->ensureSufficientFunds($amount);

            $rate = (float) $data['interest_rate'];
            $term = (int) $data['payment_term'];

            $interest = round($this->interestStrategy->calculate($amount, $rate, $term), 2);
            $totalPayable = round($amount + $interest, 2);

            $loanData = array_merge($data, [
                'interest_amount' => $interest,
                'total_payable' => $totalPayable,
                'remaining_balance' => $totalPayable,
                'status' => LoanStatus::PENDING,
            ]);

            $loan = $this->factory->create($loanData);
            $saved = $this->repository->save($loan);
            $this->installmentService->regenerateInstallments($saved);

            return $saved;
        });
    }

    public function updateLoan(Loan $loan, array $data): Loan
    {
        return DB::transaction(function () use ($loan, $data): Loan {
            $amount = (float) $data['amount'];
            $additionalRequired = round($amount - (float) $loan->amount, 2);
            if ($additionalRequired > 0) {
                $this->ensureSufficientFunds($additionalRequired);
            }

            $rate = (float) $data['interest_rate'];
            $term = (int) $data['payment_term'];

            $interest = round($this->interestStrategy->calculate($amount, $rate, $term), 2);
            $totalPayable = round($amount + $interest, 2);

            $paidAmount = round((float) $loan->total_payable - (float) $loan->remaining_balance, 2);
            $remainingBalance = round($totalPayable - $paidAmount, 2);

            if ($remainingBalance < 0) {
                $remainingBalance = 0;
            }

            $loan->fill(array_merge($data, [
                'interest_amount' => $interest,
                'total_payable' => $totalPayable,
                'remaining_balance' => $remainingBalance,
                'status' => $remainingBalance <= 0
                    ? LoanStatus::PAID
                    : (Carbon::parse($data['due_date'])->isPast() ? LoanStatus::OVERDUE : LoanStatus::PENDING),
            ]));

            $saved = $this->repository->save($loan);

            if ($saved->payments()->exists()) {
                $this->installmentService->regenerateInstallments($saved);
                foreach ($saved->payments()->orderBy('payment_date')->orderBy('id')->get() as $payment) {
                    $payment->allocations()->delete();
                    $this->installmentService->allocatePayment($payment);
                }
            } else {
                $this->installmentService->regenerateInstallments($saved);
                $this->installmentService->recalculateStatuses($saved);
            }

            return $saved;
        });
    }

    private function ensureSufficientFunds(float $requiredAmount): void
    {
        $availableFunds = $this->availableFundBalance();
        if ($requiredAmount > $availableFunds) {
            throw new InsufficientFundsException($availableFunds);
        }
    }

    private function availableFundBalance(): float
    {
        $deposits = (float) Fund::where('type', 'deposit')->sum('amount');
        $withdrawals = (float) Fund::where('type', 'withdrawal')->sum('amount');

        return max(0, round($deposits - $withdrawals, 2));
    }
}

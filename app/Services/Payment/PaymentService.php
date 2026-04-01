<?php

namespace App\Services\Payment;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Fund;
use App\Repositories\PaymentRepository;
use App\Services\Loan\LoanInstallmentService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected PaymentRepository $repository,
        protected LoanInstallmentService $installmentService
    ) {}

    public function createPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data): Payment {
            return $this->repository->create($data);
        });
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            // 1. Revert old schedule allocation and loan balance impact.
            $this->installmentService->reversePayment($payment);

            // 2. Update payment details
            $payment->fill($data);
            $payment->save();

            // 3. Apply new schedule allocation and recalculate loan balance.
            $this->installmentService->allocatePayment($payment);

            $newLoan = Loan::find($data['loan_id']);

            $loanId = $newLoan?->id ?? $payment->loan_id;
            $description = "Payment for Loan #{$loanId} - Ref: " . ($payment->reference_number ?? 'N/A');

            // 4. Update related Transaction
            $transaction = Transaction::where('reference_type', Payment::class)
                ->where('reference_id', $payment->id)
                ->first();

            if ($transaction) {
                $transaction->update([
                    'amount' => $payment->amount,
                    'description' => $description,
                ]);
            }

            // 5. Update related Fund entry
            $fund = Fund::where('reference_type', Payment::class)
                ->where('reference_id', $payment->id)
                ->first();

            if ($fund) {
                $fund->update([
                    'amount' => $payment->amount,
                    'date' => $payment->payment_date ?? now(),
                    'description' => $description,
                ]);
            }

            return $payment;
        });
    }
}

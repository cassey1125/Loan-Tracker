<?php

namespace App\Services\Payment;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Fund;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(protected PaymentRepository $repository) {}

    public function createPayment(array $data): Payment
    {
        return $this->repository->create($data);
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            // 1. Revert effect of old payment on the old loan
            $oldLoan = $payment->loan;
            if ($oldLoan) {
                $oldLoan->remaining_balance = round((float) $oldLoan->remaining_balance + (float) $payment->amount, 2);
                $oldLoan->save();
            }

            // 2. Update payment details
            $payment->fill($data);
            $payment->save();

            // 3. Apply effect of new payment on the new loan
            $newLoan = $payment->loan ?: Loan::find($data['loan_id']);
            if ($newLoan) {
                $newBalance = round((float) $newLoan->remaining_balance - (float) $payment->amount, 2);
                $newLoan->remaining_balance = max(0, $newBalance);
                $newLoan->save();
            }

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

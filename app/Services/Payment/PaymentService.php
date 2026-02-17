<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Repositories\PaymentRepository;

class PaymentService
{
    public function __construct(protected PaymentRepository $repository) {}

    public function createPayment(array $data): Payment
    {
        return $this->repository->create($data);
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        // 1. Revert effect of old payment on the old loan
        $oldLoan = $payment->loan;
        if ($oldLoan) {
            $oldLoan->remaining_balance += $payment->amount;
            $oldLoan->save();
        }

        // 2. Update payment details
        $payment->fill($data);
        $payment->save();

        // 3. Apply effect of new payment on the new loan
        $newLoan = $payment->loan; // Relationship should reflect new loan_id
        if (!$newLoan) {
             $newLoan = \App\Models\Loan::find($data['loan_id']);
        }
        
        if ($newLoan) {
            $newLoan->remaining_balance -= $payment->amount;
            if ($newLoan->remaining_balance < 0) {
                $newLoan->remaining_balance = 0;
            }
            $newLoan->save();
        }
        
        // 4. Update related Transaction
        $transaction = \App\Models\Transaction::where('reference_type', Payment::class)
            ->where('reference_id', $payment->id)
            ->first();
            
        if ($transaction) {
            $transaction->update([
                'amount' => $payment->amount,
                'description' => "Payment for Loan #{$newLoan->id} - Ref: " . ($payment->reference_number ?? 'N/A'),
            ]);
        }

        // 5. Update related Fund entry
        $fund = \App\Models\Fund::where('reference_type', Payment::class)
            ->where('reference_id', $payment->id)
            ->first();
            
        if ($fund) {
            $fund->update([
                'amount' => $payment->amount,
                'date' => $payment->payment_date ?? now(),
                'description' => "Payment for Loan #{$newLoan->id} - Ref: " . ($payment->reference_number ?? 'N/A'),
            ]);
        }

        return $payment;
    }
}

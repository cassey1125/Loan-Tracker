<?php

namespace App\Observers;

use App\Models\Fund;
use App\Models\Payment;
use App\Models\Transaction;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $loan = $payment->loan;
        
        // Update loan balance
        $loan->remaining_balance -= $payment->amount;
        if ($loan->remaining_balance < 0) {
            $loan->remaining_balance = 0;
        }
        $loan->save();

        // Record payment as income
        Transaction::create([
            'type' => 'income',
            'amount' => $payment->amount,
            'description' => "Payment for Loan #{$loan->id} - Ref: " . ($payment->reference_number ?? 'N/A'),
            'reference_id' => $payment->id,
            'reference_type' => Payment::class,
        ]);

        // Record payment as a fund deposit
        Fund::create([
            'date' => $payment->payment_date ?? now(),
            'amount' => $payment->amount,
            'type' => 'deposit',
            'description' => "Payment for Loan #{$loan->id} - Ref: " . ($payment->reference_number ?? 'N/A'),
            'reference_id' => $payment->id,
            'reference_type' => Payment::class,
        ]);
    }
}

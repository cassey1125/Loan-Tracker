<?php

namespace App\Observers;

use App\Models\Fund;
use App\Models\Loan;
use App\Models\Transaction;

class LoanObserver
{
    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        // Record loan release as an expense
        Transaction::create([
            'type' => 'expense',
            'amount' => $loan->amount,
            'description' => "Loan release for Loan #{$loan->id}",
            'reference_id' => $loan->id,
            'reference_type' => Loan::class,
        ]);

        // Record loan release as a fund withdrawal
        Fund::create([
            'date' => now(),
            'amount' => $loan->amount,
            'type' => 'withdrawal',
            'description' => "Loan release for Loan #{$loan->id}",
            'reference_id' => $loan->id,
            'reference_type' => Loan::class,
        ]);
    }

    /**
     * Handle the Loan "updating" event.
     */
    public function updating(Loan $loan): void
    {
        // Auto update status to paid when balance is 0
        if ($loan->isDirty('remaining_balance') && $loan->remaining_balance <= 0) {
            $loan->status = \App\Enums\LoanStatus::PAID;
        }
    }
}

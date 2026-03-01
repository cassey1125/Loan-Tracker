<?php

namespace App\Observers;

use App\Models\Fund;
use App\Models\Loan;
use App\Models\Transaction;
use App\Services\Monitoring\AuditLogger;

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

        AuditLogger::log('created', $loan, null, $loan->attributesToArray());
    }

    /**
     * Handle the Loan "updating" event.
     */
    public function updating(Loan $loan): void
    {
        if ($loan->isDirty('remaining_balance')) {
            if ($loan->remaining_balance <= 0) {
                $loan->status = \App\Enums\LoanStatus::PAID;
            } else {
                $loan->status = $loan->due_date && $loan->due_date->isPast()
                    ? \App\Enums\LoanStatus::OVERDUE
                    : \App\Enums\LoanStatus::PENDING;
            }
        }
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        $changes = AuditLogger::onlyDirtyAttributes($loan);
        if (!empty($changes['before']) || !empty($changes['after'])) {
            AuditLogger::log('updated', $loan, $changes['before'], $changes['after']);
        }

        if (!$loan->wasChanged('amount')) {
            return;
        }

        Transaction::where('reference_type', Loan::class)
            ->where('reference_id', $loan->id)
            ->update([
                'amount' => $loan->amount,
                'description' => "Loan release for Loan #{$loan->id}",
            ]);

        Fund::where('reference_type', Loan::class)
            ->where('reference_id', $loan->id)
            ->update([
                'amount' => $loan->amount,
                'description' => "Loan release for Loan #{$loan->id}",
            ]);
    }

    public function deleted(Loan $loan): void
    {
        AuditLogger::log('deleted', $loan, $loan->attributesToArray(), null);
    }
}

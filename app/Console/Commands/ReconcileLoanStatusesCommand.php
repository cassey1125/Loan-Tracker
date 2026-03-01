<?php

namespace App\Console\Commands;

use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Console\Command;

class ReconcileLoanStatusesCommand extends Command
{
    protected $signature = 'loans:reconcile-statuses';

    protected $description = 'Reconcile loan statuses based on due date and remaining balance.';

    public function handle(): int
    {
        $updated = 0;

        Loan::query()->chunkById(200, function ($loans) use (&$updated) {
            foreach ($loans as $loan) {
                $targetStatus = $loan->remaining_balance <= 0
                    ? LoanStatus::PAID
                    : ($loan->due_date && $loan->due_date->isPast() ? LoanStatus::OVERDUE : LoanStatus::PENDING);

                if ($loan->status !== $targetStatus) {
                    $loan->status = $targetStatus;
                    $loan->save();
                    $updated++;
                }
            }
        });

        $this->info("Loan status reconciliation complete. Updated {$updated} loan(s).");

        return self::SUCCESS;
    }
}

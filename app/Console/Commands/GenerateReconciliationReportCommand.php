<?php

namespace App\Console\Commands;

use App\Models\Fund;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\ReconciliationReport;
use App\Services\Monitoring\FinancialHealthService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateReconciliationReportCommand extends Command
{
    protected $signature = 'financial:reconcile {--month=}';

    protected $description = 'Generate monthly reconciliation report with mismatch flags.';

    public function handle(FinancialHealthService $healthService): int
    {
        $month = $this->option('month');
        $start = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : now()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $loanPrincipalTotal = (float) Loan::whereBetween('created_at', [$start, $end])->sum('amount');
        $loanInterestTotal = (float) Loan::whereBetween('created_at', [$start, $end])->sum('interest_amount');
        $paymentsTotal = (float) Payment::whereBetween('payment_date', [$start, $end])->sum('amount');
        $fundDepositsTotal = (float) Fund::where('type', 'deposit')->whereBetween('date', [$start, $end])->sum('amount');
        $fundWithdrawalsTotal = (float) Fund::where('type', 'withdrawal')->whereBetween('date', [$start, $end])->sum('amount');

        $health = $healthService->healthSummary();
        $mismatches = [];

        if ($health['payment_fund_mismatch'] !== 0.0) {
            $mismatches[] = [
                'type' => 'payment_fund_mismatch',
                'amount' => $health['payment_fund_mismatch'],
            ];
        }

        foreach (['negative_loans', 'payments_missing_fund', 'payments_missing_transaction', 'loans_missing_fund', 'loans_missing_transaction', 'failed_jobs'] as $flag) {
            if (($health[$flag] ?? 0) > 0) {
                $mismatches[] = ['type' => $flag, 'count' => $health[$flag]];
            }
        }

        ReconciliationReport::updateOrCreate(
            [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
            [
                'loan_principal_total' => round($loanPrincipalTotal, 2),
                'loan_interest_total' => round($loanInterestTotal, 2),
                'payments_total' => round($paymentsTotal, 2),
                'fund_deposits_total' => round($fundDepositsTotal, 2),
                'fund_withdrawals_total' => round($fundWithdrawalsTotal, 2),
                'calculated_fund_net' => round($fundDepositsTotal - $fundWithdrawalsTotal, 2),
                'mismatch_count' => count($mismatches),
                'mismatches' => $mismatches,
                'generated_by' => auth()->id(),
            ]
        );

        $this->info("Reconciliation report generated for {$start->format('F Y')}.");

        return self::SUCCESS;
    }
}

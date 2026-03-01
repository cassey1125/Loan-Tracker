<?php

namespace App\Services\Monitoring;

use App\Models\Fund;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinancialHealthService
{
    public function healthSummary(): array
    {
        $negativeLoans = Loan::where('remaining_balance', '<', 0)->count();

        $paymentsWithoutFund = Payment::where(function ($outer) {
            $outer->whereDoesntHave('loan')
                ->orWhereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('funds')
                        ->whereColumn('funds.reference_id', 'payments.id')
                        ->where('funds.reference_type', Payment::class);
                });
        })->count();

        $paymentsWithoutTransaction = Payment::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('transactions')
                ->whereColumn('transactions.reference_id', 'payments.id')
                ->where('transactions.reference_type', Payment::class);
        })->count();

        $loansWithoutFund = Loan::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('funds')
                ->whereColumn('funds.reference_id', 'loans.id')
                ->where('funds.reference_type', Loan::class);
        })->count();

        $loansWithoutTransaction = Loan::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('transactions')
                ->whereColumn('transactions.reference_id', 'loans.id')
                ->where('transactions.reference_type', Loan::class);
        })->count();

        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        $systemDeposits = (float) Fund::where('type', 'deposit')->where('reference_type', Payment::class)->sum('amount');
        $paymentsTotal = (float) Payment::sum('amount');
        $paymentFundMismatch = round($paymentsTotal - $systemDeposits, 2);

        return [
            'negative_loans' => $negativeLoans,
            'payments_missing_fund' => $paymentsWithoutFund,
            'payments_missing_transaction' => $paymentsWithoutTransaction,
            'loans_missing_fund' => $loansWithoutFund,
            'loans_missing_transaction' => $loansWithoutTransaction,
            'failed_jobs' => $failedJobs,
            'payment_fund_mismatch' => $paymentFundMismatch,
            'has_critical_issues' => $negativeLoans > 0
                || $paymentsWithoutFund > 0
                || $paymentsWithoutTransaction > 0
                || $loansWithoutFund > 0
                || $loansWithoutTransaction > 0
                || $failedJobs > 0
                || $paymentFundMismatch !== 0.0,
        ];
    }
}

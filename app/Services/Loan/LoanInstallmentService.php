<?php

namespace App\Services\Loan;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Carbon\Carbon;

class LoanInstallmentService
{
    public function regenerateInstallments(Loan $loan): void
    {
        $loan->installments()->delete();

        $installmentCount = max(1, (int) $loan->payment_term * 2);
        $principalCents = $this->toCents((float) $loan->amount);
        $interestCents = $this->toCents((float) $loan->interest_amount);
        $totalCents = $this->toCents((float) $loan->total_payable);

        [$principalBase, $principalLast] = $this->splitBaseAndLast($principalCents, $installmentCount);
        [$interestBase, $interestLast] = $this->splitBaseAndLast($interestCents, $installmentCount);
        [$totalBase, $totalLast] = $this->splitBaseAndLast($totalCents, $installmentCount);

        $startDate = $loan->created_at ? Carbon::parse($loan->created_at) : now();
        $finalDueDate = Carbon::parse($loan->due_date);

        $rows = [];
        for ($i = 1; $i <= $installmentCount; $i++) {
            $principalTerm = $i === $installmentCount ? $principalLast : $principalBase;
            $interestTerm = $i === $installmentCount ? $interestLast : $interestBase;
            $amountTerm = $i === $installmentCount ? $totalLast : $totalBase;

            $computedDate = $startDate->copy()->addDays($i * 15)->startOfDay();
            $dueDate = $i === $installmentCount
                ? $finalDueDate->copy()->startOfDay()
                : ($computedDate->gt($finalDueDate) ? $finalDueDate->copy()->startOfDay() : $computedDate);

            $rows[] = [
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => $dueDate->toDateString(),
                'amount_due' => $this->toDecimal($amountTerm),
                'principal_due' => $this->toDecimal($principalTerm),
                'interest_due' => $this->toDecimal($interestTerm),
                'amount_paid' => 0,
                'status' => 'pending',
                'paid_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        LoanInstallment::insert($rows);
    }

    public function allocatePayment(Payment $payment): void
    {
        $loan = $payment->loan()->lockForUpdate()->first();
        if (!$loan) {
            return;
        }

        if (!$loan->installments()->exists()) {
            $this->regenerateInstallments($loan);
        }

        $remainingCents = $this->toCents((float) $payment->amount);

        $installments = $loan->installments()
            ->lockForUpdate()
            ->orderBy('due_date')
            ->orderBy('installment_number')
            ->get();

        foreach ($installments as $installment) {
            if ($remainingCents <= 0) {
                break;
            }

            $dueCents = $this->toCents((float) $installment->amount_due);
            $paidCents = $this->toCents((float) $installment->amount_paid);
            $unpaidCents = max(0, $dueCents - $paidCents);

            if ($unpaidCents === 0) {
                continue;
            }

            $allocationCents = min($remainingCents, $unpaidCents);
            $newPaidCents = $paidCents + $allocationCents;

            $installment->amount_paid = $this->toDecimal($newPaidCents);
            if ($newPaidCents >= $dueCents) {
                $installment->status = 'paid';
                $installment->paid_at = now();
            } else {
                $installment->status = 'partial';
                $installment->paid_at = null;
            }
            $installment->save();

            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'loan_installment_id' => $installment->id,
                'amount' => $this->toDecimal($allocationCents),
            ]);

            $remainingCents -= $allocationCents;
        }

        if ($remainingCents > 0) {
            throw new \RuntimeException('Payment allocation overflow detected.');
        }

        $this->refreshLoanBalanceAndStatus($loan);
    }

    public function reversePayment(Payment $payment): void
    {
        $loan = $payment->loan()->lockForUpdate()->first();
        if (!$loan) {
            return;
        }

        $allocations = $payment->allocations()
            ->with('installment')
            ->orderByDesc('id')
            ->get();

        foreach ($allocations as $allocation) {
            $installment = $allocation->installment;
            if (!$installment) {
                continue;
            }

            $installment = LoanInstallment::whereKey($installment->id)->lockForUpdate()->first();
            if (!$installment) {
                continue;
            }

            $paidCents = $this->toCents((float) $installment->amount_paid);
            $allocationCents = $this->toCents((float) $allocation->amount);
            $dueCents = $this->toCents((float) $installment->amount_due);

            $newPaidCents = max(0, $paidCents - $allocationCents);
            $installment->amount_paid = $this->toDecimal($newPaidCents);

            if ($newPaidCents <= 0) {
                $installment->status = Carbon::parse($installment->due_date)->isPast() ? 'overdue' : 'pending';
                $installment->paid_at = null;
            } elseif ($newPaidCents < $dueCents) {
                $installment->status = 'partial';
                $installment->paid_at = null;
            } else {
                $installment->status = 'paid';
            }

            $installment->save();
        }

        $payment->allocations()->delete();
        $this->refreshLoanBalanceAndStatus($loan);
    }

    public function recalculateStatuses(Loan $loan): void
    {
        $loan->installments()->lockForUpdate()->get()->each(function (LoanInstallment $installment): void {
            $dueCents = $this->toCents((float) $installment->amount_due);
            $paidCents = $this->toCents((float) $installment->amount_paid);

            if ($paidCents >= $dueCents) {
                $installment->status = 'paid';
                $installment->paid_at = $installment->paid_at ?: now();
            } elseif ($paidCents > 0) {
                $installment->status = 'partial';
                $installment->paid_at = null;
            } else {
                $installment->status = Carbon::parse($installment->due_date)->isPast() ? 'overdue' : 'pending';
                $installment->paid_at = null;
            }

            $installment->save();
        });

        $this->refreshLoanBalanceAndStatus($loan->refresh());
    }

    private function refreshLoanBalanceAndStatus(Loan $loan): void
    {
        $balanceCents = $this->toCents((float) $loan->installments()->sum('amount_due'))
            - $this->toCents((float) $loan->installments()->sum('amount_paid'));
        $balanceCents = max(0, $balanceCents);

        $loan->remaining_balance = $this->toDecimal($balanceCents);
        $loan->status = $balanceCents === 0
            ? LoanStatus::PAID
            : (Carbon::parse($loan->due_date)->isPast() ? LoanStatus::OVERDUE : LoanStatus::PENDING);
        $loan->save();
    }

    private function splitBaseAndLast(int $totalCents, int $count): array
    {
        $base = intdiv($totalCents, $count);
        $last = $base + ($totalCents - ($base * $count));

        return [$base, $last];
    }

    private function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function toDecimal(int $cents): float
    {
        return round($cents / 100, 2);
    }
}

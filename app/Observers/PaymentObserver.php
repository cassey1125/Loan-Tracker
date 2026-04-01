<?php

namespace App\Observers;

use App\Models\Fund;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\Loan\LoanInstallmentService;
use App\Services\Monitoring\AuditLogger;

class PaymentObserver
{
    public function __construct(protected LoanInstallmentService $installmentService)
    {
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $loan = $payment->loan;
        if (!$loan) {
            return;
        }

        $this->installmentService->allocatePayment($payment);

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

        AuditLogger::log('created', $payment, null, $payment->attributesToArray());
    }

    public function updated(Payment $payment): void
    {
        $changes = AuditLogger::onlyDirtyAttributes($payment);
        if (!empty($changes['before']) || !empty($changes['after'])) {
            AuditLogger::log('updated', $payment, $changes['before'], $changes['after']);
        }
    }

    public function deleted(Payment $payment): void
    {
        $this->installmentService->reversePayment($payment);

        Transaction::where('reference_type', Payment::class)
            ->where('reference_id', $payment->id)
            ->delete();

        Fund::where('reference_type', Payment::class)
            ->where('reference_id', $payment->id)
            ->delete();

        AuditLogger::log('deleted', $payment, $payment->attributesToArray(), null);
    }
}

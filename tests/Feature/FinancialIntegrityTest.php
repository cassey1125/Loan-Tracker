<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;
use App\Services\Loan\LoanService;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_update_keeps_loan_balance_and_linked_entries_in_sync(): void
    {
        $borrower = Borrower::factory()->create();

        $loan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'amount' => 1000,
            'interest_rate' => 5,
            'payment_term' => 1,
            'interest_amount' => 0,
            'total_payable' => 1000,
            'remaining_balance' => 1000,
            'due_date' => now()->addMonth(),
            'status' => LoanStatus::PENDING,
        ]);

        $payment = Payment::create([
            'loan_id' => $loan->id,
            'amount' => 200,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        app(PaymentService::class)->updatePayment($payment, [
            'loan_id' => $loan->id,
            'amount' => 150,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'reference_number' => 'UPD-001',
            'notes' => null,
        ]);

        $loan->refresh();
        $payment->refresh();

        $this->assertEquals('850.00', $loan->remaining_balance);

        $this->assertDatabaseHas('transactions', [
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'amount' => 150,
        ]);

        $this->assertDatabaseHas('funds', [
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'type' => 'deposit',
            'amount' => 150,
        ]);
    }

    public function test_updating_loan_amount_updates_linked_fund_and_transaction_amounts(): void
    {
        $borrower = Borrower::factory()->create();

        $loan = app(LoanService::class)->createLoan([
            'borrower_id' => $borrower->id,
            'amount' => 1000,
            'interest_rate' => 5,
            'payment_term' => 1,
            'due_date' => now()->addMonth()->toDateString(),
        ]);

        app(LoanService::class)->updateLoan($loan, [
            'borrower_id' => $borrower->id,
            'amount' => 1250,
            'interest_rate' => 5,
            'payment_term' => 1,
            'due_date' => now()->addMonth()->toDateString(),
        ]);

        $this->assertDatabaseHas('transactions', [
            'reference_type' => Loan::class,
            'reference_id' => $loan->id,
            'type' => 'expense',
            'amount' => 1250,
        ]);

        $this->assertDatabaseHas('funds', [
            'reference_type' => Loan::class,
            'reference_id' => $loan->id,
            'type' => 'withdrawal',
            'amount' => 1250,
        ]);
    }
}

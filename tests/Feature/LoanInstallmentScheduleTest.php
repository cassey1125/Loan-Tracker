<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Models\Borrower;
use App\Models\Fund;
use App\Models\Loan;
use App\Models\Payment;
use App\Services\Loan\LoanService;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanInstallmentScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_creation_generates_semi_monthly_installments_with_exact_totals(): void
    {
        $borrower = Borrower::factory()->create();

        Fund::create([
            'date' => now()->toDateString(),
            'amount' => 100000,
            'type' => 'deposit',
            'description' => 'Capital',
        ]);

        $loan = app(LoanService::class)->createLoan([
            'borrower_id' => $borrower->id,
            'amount' => 10000,
            'interest_rate' => 10,
            'payment_term' => 3,
            'due_date' => now()->addMonths(3)->toDateString(),
        ]);

        $installments = $loan->installments()->orderBy('installment_number')->get();

        $this->assertCount(6, $installments);
        $this->assertEquals('13000.00', number_format($installments->sum('amount_due'), 2, '.', ''));
        $this->assertEquals($loan->due_date->toDateString(), $installments->last()->due_date->toDateString());
        $this->assertTrue($installments->every(fn ($row) => (float) $row->amount_paid === 0.0));
    }

    public function test_payment_allocates_to_oldest_terms_and_updates_loan_balance(): void
    {
        $borrower = Borrower::factory()->create();

        Fund::create([
            'date' => now()->toDateString(),
            'amount' => 100000,
            'type' => 'deposit',
            'description' => 'Capital',
        ]);

        $loan = app(LoanService::class)->createLoan([
            'borrower_id' => $borrower->id,
            'amount' => 10000,
            'interest_rate' => 10,
            'payment_term' => 2,
            'due_date' => now()->addMonths(2)->toDateString(),
        ]);

        // Total payable = 12,000 over 4 terms => 3,000 each.
        $payment = app(PaymentService::class)->createPayment([
            'loan_id' => $loan->id,
            'amount' => 4500,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'reference_number' => 'ALLOC-001',
            'notes' => null,
        ]);

        $loan->refresh();
        $terms = $loan->installments()->orderBy('installment_number')->get();

        $this->assertEquals('7500.00', number_format((float) $loan->remaining_balance, 2, '.', ''));
        $this->assertEquals('paid', $terms[0]->status);
        $this->assertEquals('3000.00', number_format((float) $terms[0]->amount_paid, 2, '.', ''));
        $this->assertEquals('partial', $terms[1]->status);
        $this->assertEquals('1500.00', number_format((float) $terms[1]->amount_paid, 2, '.', ''));
        $this->assertEquals('pending', $terms[2]->status);
        $this->assertEquals('pending', $terms[3]->status);

        $this->assertEquals('4500.00', number_format((float) $payment->allocations()->sum('amount'), 2, '.', ''));
    }

    public function test_updating_payment_reallocates_schedule_and_preserves_consistency(): void
    {
        $borrower = Borrower::factory()->create();

        Fund::create([
            'date' => now()->toDateString(),
            'amount' => 100000,
            'type' => 'deposit',
            'description' => 'Capital',
        ]);

        $loan = app(LoanService::class)->createLoan([
            'borrower_id' => $borrower->id,
            'amount' => 1000,
            'interest_rate' => 5,
            'payment_term' => 1,
            'due_date' => now()->addMonth()->toDateString(),
        ]);

        $payment = Payment::create([
            'loan_id' => $loan->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        app(PaymentService::class)->updatePayment($payment, [
            'loan_id' => $loan->id,
            'amount' => 300,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'reference_number' => 'REALLOC-001',
            'notes' => null,
        ]);

        $loan->refresh();

        $this->assertEquals('750.00', number_format((float) $loan->remaining_balance, 2, '.', ''));
        $this->assertEquals(LoanStatus::PENDING, $loan->status);
        $this->assertEquals('300.00', number_format((float) $payment->allocations()->sum('amount'), 2, '.', ''));
    }
}

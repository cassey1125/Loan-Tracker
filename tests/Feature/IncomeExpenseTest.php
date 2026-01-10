<?php

namespace Tests\Feature;

use App\Livewire\IncomeExpense;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class IncomeExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_expense_component_renders()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/income-expense')
            ->assertStatus(200)
            ->assertSeeLivewire('income-expense');
    }

    public function test_income_expense_calculations()
    {
        $user = User::factory()->create();
        $borrower = Borrower::factory()->create();

        // Create a loan (Expense) in the current month
        $loanAmount = 1000;
        $loan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'amount' => $loanAmount,
            'created_at' => Carbon::now(),
        ]);

        // Create a payment (Income) in the current month
        $paymentAmount = 200;
        Payment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => $paymentAmount,
            'payment_date' => Carbon::now(),
        ]);

        // Create a loan and payment outside the current month (should not be counted by default)
        $oldLoan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(2),
        ]);

        Payment::factory()->create([
            'loan_id' => $oldLoan->id,
            'amount' => 100,
            'payment_date' => Carbon::now()->subMonths(2),
        ]);

        Livewire::actingAs($user)
            ->test(IncomeExpense::class)
            ->assertSet('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'))
            ->assertSet('endDate', Carbon::now()->endOfMonth()->format('Y-m-d'))
            ->assertViewHas('totalIncome', $paymentAmount)
            ->assertViewHas('totalExpenses', $loanAmount)
            ->assertViewHas('netProfit', $paymentAmount - $loanAmount)
            ->assertSee(number_format($paymentAmount, 2))
            ->assertSee(number_format($loanAmount, 2));
    }

    public function test_income_expense_date_filtering()
    {
        $user = User::factory()->create();
        $borrower = Borrower::factory()->create();

        // Old data
        $oldLoanAmount = 5000;
        $oldLoan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'amount' => $oldLoanAmount,
            'created_at' => Carbon::parse('2023-01-15'),
        ]);

        $oldPaymentAmount = 1000;
        Payment::factory()->create([
            'loan_id' => $oldLoan->id,
            'amount' => $oldPaymentAmount,
            'payment_date' => Carbon::parse('2023-01-20'),
        ]);

        Livewire::actingAs($user)
            ->test(IncomeExpense::class)
            ->set('startDate', '2023-01-01')
            ->set('endDate', '2023-01-31')
            ->assertViewHas('totalIncome', $oldPaymentAmount)
            ->assertViewHas('totalExpenses', $oldLoanAmount)
            ->assertViewHas('netProfit', $oldPaymentAmount - $oldLoanAmount);
    }
}

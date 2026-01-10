<?php

namespace Tests\Feature;

use App\Livewire\Loans;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_loan_via_livewire_component()
    {
        $user = User::factory()->create();
        $borrower = Borrower::factory()->create();

        Livewire::actingAs($user)
            ->test(Loans::class)
            ->set('borrower_id', $borrower->id)
            ->set('amount', 1000)
            ->set('interest_rate', 5)
            ->set('due_date', now()->addMonth()->format('Y-m-d'))
            ->set('payment_term', 4) // Reduced term for simpler calc
            ->call('createLoan')
            ->assertHasNoErrors()
            ->assertSee('Loan created successfully');

        // Calc: 1000 * (5/100) * 4 = 200 interest.
        // Total: 1200.

        $this->assertDatabaseHas('loans', [
            'borrower_id' => $borrower->id,
            'amount' => 1000,
            'interest_rate' => 5,
            'payment_term' => 4,
            'interest_amount' => 200, 
            'total_payable' => 1200,
            'remaining_balance' => 1200,
            'status' => 'pending',
        ]);

        $loan = Loan::first();

        $this->assertDatabaseHas('transactions', [
            'type' => 'expense',
            'amount' => 1000,
            'reference_id' => $loan->id,
            'reference_type' => Loan::class,
        ]);
    }

    public function test_validation_rules()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Loans::class)
            ->set('amount', '')
            ->call('createLoan')
            ->assertHasErrors(['amount' => 'required']);
    }
}

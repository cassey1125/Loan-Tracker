<?php

namespace Tests\Feature;

use App\Livewire\Loans;
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

        Livewire::actingAs($user)
            ->test(Loans::class)
            ->set('amount', 1000)
            ->set('interest_rate', 10)
            ->set('due_date', now()->addMonth()->format('Y-m-d'))
            ->set('payment_term', 12)
            ->call('createLoan')
            ->assertHasNoErrors()
            ->assertSee('Loan created successfully');

        $this->assertDatabaseHas('loans', [
            'amount' => 1000,
            'interest_rate' => 10,
            'payment_term' => 12,
            'interest_amount' => 100, // 10% of 1000 (Flat Rate Strategy)
            'total_payable' => 1100,
            'remaining_balance' => 1100,
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

<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Funds;
use App\Livewire\Loans;
use App\Livewire\Payments;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinancialAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_create_loan(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $borrower = Borrower::factory()->create();

        Livewire::actingAs($staff)
            ->test(Loans::class)
            ->set('borrower_id', $borrower->id)
            ->set('amount', 1000)
            ->set('interest_rate', 5)
            ->set('due_date', now()->addMonth()->format('Y-m-d'))
            ->set('payment_term', 1)
            ->call('createLoan')
            ->assertForbidden();
    }

    public function test_staff_cannot_create_payment(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $borrower = Borrower::factory()->create();
        $loan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'remaining_balance' => 1000,
        ]);

        Livewire::actingAs($staff)
            ->test(Payments::class)
            ->set('loan_id', $loan->id)
            ->set('amount', 100)
            ->set('payment_method', 'cash')
            ->set('payment_date', now()->format('Y-m-d'))
            ->call('createPayment')
            ->assertForbidden();
    }

    public function test_staff_cannot_create_fund_transaction(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);

        Livewire::actingAs($staff)
            ->test(Funds::class)
            ->set('amount', 100)
            ->set('transactionType', 'deposit')
            ->set('date', now()->format('Y-m-d'))
            ->set('description', 'Test')
            ->call('saveFund')
            ->assertForbidden();
    }
}

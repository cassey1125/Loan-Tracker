<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Borrowers\BorrowerEdit;
use App\Livewire\Borrowers\BorrowerList;
use App\Livewire\Funds;
use App\Livewire\Loans;
use App\Livewire\MotorRentals;
use App\Livewire\Payments;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\MotorRental;
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

    public function test_staff_cannot_edit_or_delete_borrowers(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $borrower = Borrower::factory()->create();

        Livewire::actingAs($staff)
            ->test(BorrowerEdit::class, ['borrower' => $borrower])
            ->set('first_name', 'Updated')
            ->set('last_name', 'Borrower')
            ->call('update')
            ->assertForbidden();

        Livewire::actingAs($staff)
            ->test(BorrowerList::class)
            ->call('delete', $borrower->id)
            ->assertForbidden();
    }

    public function test_staff_cannot_access_borrower_create_or_edit_pages(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $borrower = Borrower::factory()->create();

        $this->actingAs($staff)
            ->get(route('borrowers.create'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('borrowers.edit', $borrower))
            ->assertForbidden();
    }

    public function test_staff_cannot_create_or_delete_motor_rentals(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $rental = MotorRental::create([
            'motor_name' => 'Mio 125 - Unit 04',
            'renter_name' => 'Juan Dela Cruz',
            'rental_date' => '2026-03-11',
            'rental_days' => 2,
            'rental_end_date' => '2026-03-12',
            'notes' => 'Existing rental',
        ]);

        Livewire::actingAs($staff)
            ->test(MotorRentals::class)
            ->set('motor_name', 'Click 160 - Unit 02')
            ->set('renter_name', 'Pedro Santos')
            ->set('rental_date', '2026-03-15')
            ->set('rental_days', 3)
            ->call('saveRental')
            ->assertForbidden();

        Livewire::actingAs($staff)
            ->test(MotorRentals::class)
            ->call('deleteRentalConfirmed', $rental->id)
            ->assertForbidden();
    }
}

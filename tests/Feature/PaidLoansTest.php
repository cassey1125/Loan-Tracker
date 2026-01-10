<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaidLoansTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_loans_page_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/paid-loans');

        $response->assertStatus(200);
        $response->assertSeeLivewire('paid-loans');
    }

    public function test_paid_loans_list_shows_only_paid_loans()
    {
        $user = User::factory()->create();
        $borrower = Borrower::factory()->create();

        $paidLoan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'status' => LoanStatus::PAID,
            'amount' => 1000,
            'interest_amount' => 100,
            'total_payable' => 1100,
        ]);

        $activeLoan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'status' => LoanStatus::PENDING,
            'amount' => 2000,
        ]);

        Livewire::actingAs($user)
            ->test('paid-loans')
            ->assertSee(number_format($paidLoan->amount, 2))
            ->assertDontSee(number_format($activeLoan->amount, 2));
    }

    public function test_search_filter_works()
    {
        $user = User::factory()->create();
        
        $borrower1 = Borrower::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $borrower2 = Borrower::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $loan1 = Loan::factory()->create([
            'borrower_id' => $borrower1->id,
            'status' => LoanStatus::PAID,
            'amount' => 5000,
            'interest_amount' => 500,
        ]);

        $loan2 = Loan::factory()->create([
            'borrower_id' => $borrower2->id,
            'status' => LoanStatus::PAID,
            'amount' => 3000,
            'interest_amount' => 300,
        ]);

        Livewire::actingAs($user)
            ->test('paid-loans')
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->assertSee(number_format(5000, 2))
            ->assertDontSee(number_format(3000, 2));
    }

    public function test_total_income_calculation()
    {
        $user = User::factory()->create();
        $borrower = Borrower::factory()->create();

        Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'status' => LoanStatus::PAID,
            'interest_amount' => 500,
        ]);

        Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'status' => LoanStatus::PAID,
            'interest_amount' => 300,
        ]);

        Livewire::actingAs($user)
            ->test('paid-loans')
            ->assertSee('₱800.00'); // Total Income
    }
}

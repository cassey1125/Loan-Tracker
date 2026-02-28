<?php

namespace Tests\Feature;

use App\Livewire\Funds;
use App\Models\Fund as FundModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FundsGuardrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_withdraw_more_than_available_funds(): void
    {
        $user = User::factory()->create();

        FundModel::create([
            'date' => now()->toDateString(),
            'amount' => 500,
            'type' => 'deposit',
            'description' => 'Initial capital',
        ]);

        Livewire::actingAs($user)
            ->test(Funds::class)
            ->set('amount', 700)
            ->set('transactionType', 'withdrawal')
            ->set('date', now()->toDateString())
            ->call('saveFund')
            ->assertHasErrors(['amount']);

        $this->assertEquals(1, FundModel::count());
    }
}


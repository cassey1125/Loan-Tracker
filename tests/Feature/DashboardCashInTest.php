<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use App\Livewire\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardCashInTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_in_reflects_fund_deposits()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a fund deposit
        $depositAmount = 5000;
        Fund::create([
            'date' => now(),
            'amount' => $depositAmount,
            'type' => 'deposit',
            'description' => 'Test Deposit',
        ]);

        // Create a fund withdrawal (should not be included)
        Fund::create([
            'date' => now(),
            'amount' => 1000,
            'type' => 'withdrawal',
            'description' => 'Test Withdrawal',
        ]);

        // Get the dashboard component
        $component = Livewire::test(Dashboard::class);
        
        // Access the lineChartData
        $lineChartData = $component->viewData('lineChartData');
        
        // Check the 'Cash In' series
        $cashInSeries = null;
        foreach ($lineChartData['series'] as $series) {
            if ($series['name'] === 'Cash In') {
                $cashInSeries = $series;
                break;
            }
        }

        $this->assertNotNull($cashInSeries, 'Cash In series not found');
        
        // The last data point should correspond to the current month
        $lastDataPoint = end($cashInSeries['data']);
        
        $this->assertEquals($depositAmount, $lastDataPoint, 'Cash In amount should match deposit amount');
    }
}

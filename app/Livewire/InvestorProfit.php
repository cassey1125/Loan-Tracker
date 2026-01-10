<?php

namespace App\Livewire;

use App\Models\Loan;
use Livewire\Component;
use Livewire\WithPagination;

class InvestorProfit extends Component
{
    use WithPagination;

    public function render()
    {
        // Get all loans that have interest generated
        // We might want to filter by status (e.g., active, paid) or just show all potential profit
        // The user request is "investor profit", usually implies realized or expected profit.
        // Given the context of "loan seeds", likely all loans.
        
        $loans5 = Loan::where('interest_rate', 5)->get();
        $loans7 = Loan::where('interest_rate', 7)->get();

        $totalProfit5_Investor1 = 0;
        $totalProfit5_Investor2 = 0;
        
        foreach ($loans5 as $loan) {
            $totalProfit5_Investor1 += $loan->investor1_interest;
            $totalProfit5_Investor2 += $loan->investor2_interest;
        }

        $totalProfit7_Investor1 = 0;
        $totalProfit7_Investor2 = 0;

        foreach ($loans7 as $loan) {
            $totalProfit7_Investor1 += $loan->investor1_interest;
            $totalProfit7_Investor2 += $loan->investor2_interest;
        }

        return view('livewire.investor-profit', [
            'loans5' => $loans5,
            'loans7' => $loans7,
            'summary' => [
                'rate_5' => [
                    'investor1' => $totalProfit5_Investor1, // 4%
                    'investor2' => $totalProfit5_Investor2, // 1%
                    'total_interest' => $loans5->sum('interest_amount'),
                ],
                'rate_7' => [
                    'investor1' => $totalProfit7_Investor1, // 5%
                    'investor2' => $totalProfit7_Investor2, // 2%
                    'total_interest' => $loans7->sum('interest_amount'),
                ],
            ]
        ]);
    }
}

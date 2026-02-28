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
        $rates = [5, 7, 10];
        $loanGroups = [];
        $summary = [];

        foreach ($rates as $rate) {
            $loans = Loan::where('interest_rate', $rate)->get();
            $split = Loan::getInvestorRateSplitFor($rate);

            $loanGroups[$rate] = $loans;
            $summary["rate_{$rate}"] = [
                'investor1' => $loans->sum(fn (Loan $loan) => $loan->investor1_interest),
                'investor2' => $loans->sum(fn (Loan $loan) => $loan->investor2_interest),
                'total_interest' => $loans->sum('interest_amount'),
                'investor1_rate' => $split['investor1'],
                'investor2_rate' => $split['investor2'],
            ];
        }

        return view('livewire.investor-profit', [
            'rates' => $rates,
            'loanGroups' => $loanGroups,
            'summary' => $summary,
        ]);
    }
}

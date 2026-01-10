<?php

namespace App\Livewire;

use App\Repositories\LoanRepository;
use Livewire\Component;

class PaidLoans extends Component
{
    public $search = '';
    public $dateFrom;
    public $dateTo;

    public function render(LoanRepository $repository)
    {
        $loans = $repository->getPaidLoans($this->search, $this->dateFrom, $this->dateTo);
        
        // Calculate Total Income (Sum of interest_amount for the filtered loans)
        // Assuming interest_amount is the income.
        $totalIncome = $loans->sum('interest_amount');

        return view('livewire.paid-loans', [
            'loans' => $loans,
            'totalIncome' => $totalIncome,
        ]);
    }
}

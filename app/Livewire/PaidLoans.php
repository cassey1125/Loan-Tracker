<?php

namespace App\Livewire;

use App\Repositories\LoanRepository;
use Livewire\Component;

class PaidLoans extends Component
{
    public $search = '';
    public $loanId = '';
    public $dateFrom;
    public $dateTo;
    public $startDateFrom;
    public $startDateTo;

    public function render(LoanRepository $repository)
    {
        $loans = $repository->getPaidLoans(
            $this->search, 
            $this->loanId, 
            $this->dateFrom, 
            $this->dateTo,
            $this->startDateFrom,
            $this->startDateTo
        );
        
        // Calculate Total Income (Sum of interest_amount for the filtered loans)
        // Assuming interest_amount is the income.
        $totalIncome = $loans->sum('interest_amount');

        return view('livewire.paid-loans', [
            'loans' => $loans,
            'totalIncome' => $totalIncome,
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Http\Requests\StoreLoanRequest;
use App\Models\Borrower;
use App\Repositories\LoanRepository;
use App\Services\Loan\LoanService;
use Livewire\Component;

class Loans extends Component
{
    public $borrower_id;
    public $amount;
    public $interest_rate;
    public $due_date;
    public $payment_term;

    public function render(LoanRepository $repository)
    {
        return view('livewire.loans', [
            'loans' => $repository->getAll(),
            'borrowers' => Borrower::all(), // For the select dropdown
        ]);
    }

    public function createLoan(LoanService $service)
    {
        $validated = $this->validate((new StoreLoanRequest())->rules());

        $service->createLoan($validated);

        $this->reset(['borrower_id', 'amount', 'interest_rate', 'due_date', 'payment_term']);
        
        session()->flash('message', 'Loan created successfully.');
    }
}

<?php

namespace App\Livewire;

use App\Exceptions\InsufficientFundsException;
use App\Http\Requests\StoreLoanRequest;
use App\Models\Borrower;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use App\Services\Loan\LoanService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Loans extends Component
{
    public $borrower_id;
    public $amount;
    public $interest_rate;
    public $due_date;
    public $payment_term;
    public $editingLoanId = null;
    public $selectedScheduleLoanId = null;

    public function render(LoanRepository $repository)
    {
        return view('livewire.loans', [
            'loans' => $repository->getAll(),
            'borrowers' => Borrower::all(), // For the select dropdown
        ]);
    }

    public function updatedPaymentTerm()
    {
        if ($this->payment_term) {
            $startDate = $this->editingLoanId 
                ? Loan::findOrFail($this->editingLoanId)->created_at 
                : now();
                
            $this->due_date = $startDate->copy()->addMonths((int)$this->payment_term)->format('Y-m-d');
            $this->validate(['due_date' => 'required|date|after:today']);
        }
    }

    public function updatedDueDate()
    {
        if ($this->due_date) {
            $startDate = $this->editingLoanId 
                ? Loan::findOrFail($this->editingLoanId)->created_at 
                : now();
            
            $dueDate = \Carbon\Carbon::parse($this->due_date);
            
            // Calculate difference in months and round to nearest integer
            $floatDiff = $startDate->floatDiffInMonths($dueDate, false);
            $term = (int) round($floatDiff);
            
            // Ensure term is at least 1 month
            if ($term < 1) {
                $term = 1;
            }
            
            $this->payment_term = $term;
            
            // Clear any validation errors regarding date alignment
            $this->resetValidation('due_date');
            $this->resetValidation('payment_term');
        }
    }

    public function createLoan(LoanService $service)
    {
        $this->ensureCanManageFinancialRecords();

        // Ensure term is set if due date is present
        if ($this->due_date && !$this->payment_term) {
             $this->updatedDueDate();
        }

        $validated = $this->validate((new StoreLoanRequest())->rules());

        try {
            $service->createLoan($validated);
        } catch (InsufficientFundsException $e) {
            $this->addError('amount', $e->getMessage());
            $this->dispatch('swal:notify', type: 'warning', message: $e->getMessage());

            return;
        }

        $this->reset(['borrower_id', 'amount', 'interest_rate', 'due_date', 'payment_term']);
        
        session()->flash('message', 'Loan created successfully.');
        $this->dispatch('swal:notify', type: 'success', message: 'Loan created successfully.');
    }

    public function editLoan($id)
    {
        $this->ensureCanManageFinancialRecords();

        $this->editingLoanId = $id;
        $loan = Loan::findOrFail($id);
        $this->borrower_id = $loan->borrower_id;
        $this->amount = $loan->amount;
        $this->interest_rate = $loan->interest_rate;
        $this->due_date = $loan->due_date->format('Y-m-d');
        $this->payment_term = $loan->payment_term;
    }

    public function updateLoan(LoanService $service)
    {
        $this->ensureCanManageFinancialRecords();

        // Ensure term is set if due date is present
        if ($this->due_date && !$this->payment_term) {
             $this->updatedDueDate();
        }

        $rules = (new StoreLoanRequest())->rules();
        $rules['due_date'] = ['required', 'date']; // Relax constraint for update
        
        $validated = $this->validate($rules);
        
        $loan = Loan::findOrFail($this->editingLoanId);
        
        try {
            $service->updateLoan($loan, $validated);
        } catch (InsufficientFundsException $e) {
            $this->addError('amount', $e->getMessage());
            $this->dispatch('swal:notify', type: 'warning', message: $e->getMessage());

            return;
        }

        $this->cancelEdit();
        session()->flash('message', 'Loan updated successfully.');
        $this->dispatch('swal:notify', type: 'success', message: 'Loan updated successfully.');
    }

    public function deleteLoan($id)
    {
        $this->ensureCanManageFinancialRecords();

        $loan = Loan::findOrFail($id);

        DB::transaction(function () use ($loan) {
            $loan->payments()->forceDelete();
            $loan->forceDelete();
        });

        if ((int) $this->editingLoanId === (int) $id) {
            $this->cancelEdit();
        }

        if ((int) $this->selectedScheduleLoanId === (int) $id) {
            $this->selectedScheduleLoanId = null;
        }

        session()->flash('message', 'Loan deleted successfully.');
        $this->dispatch('swal:notify', type: 'success', message: 'Loan deleted successfully.');
    }

    public function cancelEdit()
    {
        $this->reset(['borrower_id', 'amount', 'interest_rate', 'due_date', 'payment_term', 'editingLoanId']);
    }

    public function viewSchedule(int $loanId): void
    {
        $this->selectedScheduleLoanId = $loanId;
    }

    public function closeSchedule(): void
    {
        $this->selectedScheduleLoanId = null;
    }

    private function ensureCanManageFinancialRecords(): void
    {
        $user = auth()->user();
        if (!$user || !$user->canManageFinancialRecords()) {
            throw new HttpException(403, 'Only owner/admin can modify financial records.');
        }
    }
}

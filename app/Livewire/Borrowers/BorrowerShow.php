<?php

namespace App\Livewire\Borrowers;

use App\Exceptions\InsufficientFundsException;
use App\Models\Borrower;
use App\Models\Loan;
use App\Services\Loan\LoanService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BorrowerShow extends Component
{
    public Borrower $borrower;
    public $editingLoanId = null;
    public $amount;
    public $interest_rate;
    public $payment_term;

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower->load('loans');
    }

    public function editLoan(int $loanId): void
    {
        $this->ensureCanManageFinancialRecords();

        $loan = Loan::findOrFail($loanId);
        $this->editingLoanId = $loan->id;
        $this->amount = $loan->amount;
        $this->interest_rate = $loan->interest_rate;
        $this->payment_term = $loan->payment_term;
    }

    public function updateLoan(LoanService $service): void
    {
        $this->ensureCanManageFinancialRecords();

        $loan = Loan::findOrFail($this->editingLoanId);

        $validated = $this->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'interest_rate' => ['required', 'numeric', 'in:5,7,10'],
            'payment_term' => ['required', 'integer', 'min:1'],
        ]);

        $startDate = $loan->created_at ?? now();
        $validated['due_date'] = $startDate->copy()->addMonths((int) $validated['payment_term'])->format('Y-m-d');

        try {
            $service->updateLoan($loan, $validated);
        } catch (InsufficientFundsException $e) {
            $this->addError('amount', $e->getMessage());
            $this->dispatch('swal:notify', type: 'warning', message: $e->getMessage());
            return;
        }

        $this->cancelEdit();
        $this->refreshBorrower();
        session()->flash('message', 'Loan updated successfully.');
        $this->dispatch('swal:notify', type: 'success', message: 'Loan updated successfully.');
    }

    public function deleteLoan(int $loanId): void
    {
        $this->ensureCanManageFinancialRecords();

        $loan = Loan::findOrFail($loanId);
        DB::transaction(function () use ($loan) {
            $loan->payments()->forceDelete();
            $loan->forceDelete();
        });
        if ((int) $this->editingLoanId === (int) $loanId) {
            $this->cancelEdit();
        }
        $this->refreshBorrower();
        session()->flash('message', 'Loan deleted successfully.');
        $this->dispatch('swal:notify', type: 'success', message: 'Loan deleted successfully.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingLoanId', 'amount', 'interest_rate', 'payment_term']);
    }

    public function render()
    {
        return view('livewire.borrowers.borrower-show');
    }

    private function refreshBorrower(): void
    {
        $this->borrower->refresh()->load('loans');
    }

    private function ensureCanManageFinancialRecords(): void
    {
        $user = auth()->user();
        if (!$user || !$user->canManageFinancialRecords()) {
            throw new HttpException(403, 'Only owner/admin can modify loans.');
        }
    }
}

<?php

namespace App\Livewire;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Loan;
use App\Models\Payment;
use App\Repositories\LoanRepository;
use App\Repositories\PaymentRepository;
use App\Services\Payment\PaymentService;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Payments extends Component
{
    use WithPagination;

    public $loan_id;
    public $amount;
    public $payment_method = 'cash';
    public $payment_date;
    public $reference_number;
    public $notes;
    public $editingPaymentId = null;

    public function mount()
    {
        $this->payment_date = date('Y-m-d');
    }

    public function createPayment(PaymentService $service)
    {
        $validated = $this->validate((new StorePaymentRequest())->rules());

        $service->createPayment($validated);

        $this->reset(['loan_id', 'amount', 'reference_number', 'notes', 'payment_method']);
        $this->payment_date = date('Y-m-d');
        $this->payment_method = 'cash';

        session()->flash('message', 'Payment recorded successfully.');
    }

    public function editPayment($id)
    {
        $this->ensureCanManageFinancialRecords();

        $this->editingPaymentId = $id;
        $payment = Payment::find($id);
        $this->loan_id = $payment->loan_id;
        $this->amount = $payment->amount;
        $this->payment_method = $payment->payment_method;
        $this->payment_date = $payment->payment_date->format('Y-m-d');
        $this->reference_number = $payment->reference_number;
        $this->notes = $payment->notes;
    }

    public function updatePayment(PaymentService $service)
    {
        $this->ensureCanManageFinancialRecords();

        $payment = Payment::findOrFail($this->editingPaymentId);

        $validated = $this->validate([
            'loan_id' => ['required', 'exists:loans,id'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($payment) {
                    $targetLoan = Loan::find($this->loan_id);
                    if (!$targetLoan) {
                        return;
                    }

                    $reversibleAmount = $payment->loan_id === $targetLoan->id ? (float) $payment->amount : 0.0;
                    $allowed = (float) $targetLoan->remaining_balance + $reversibleAmount;

                    if ((float) $value > $allowed) {
                        $fail('The payment amount cannot exceed the remaining balance allowance of ' . number_format($allowed, 2) . '.');
                    }
                },
            ],
            'payment_method' => ['required', 'string', 'in:cash,gcash,card'],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $service->updatePayment($payment, $validated);

        $this->cancelEdit();
        session()->flash('message', 'Payment updated successfully.');
    }

    public function cancelEdit()
    {
        $this->reset(['loan_id', 'amount', 'reference_number', 'notes', 'payment_method', 'editingPaymentId']);
        $this->payment_date = date('Y-m-d');
        $this->payment_method = 'cash';
    }

    public function render(PaymentRepository $paymentRepository, LoanRepository $loanRepository)
    {
        return view('livewire.payments', [
            'payments' => $paymentRepository->getAll(),
            'activeLoans' => $loanRepository->getActiveLoans(),
        ]);
    }

    private function ensureCanManageFinancialRecords(): void
    {
        $user = auth()->user();
        if (!$user || !$user->canManageFinancialRecords()) {
            throw new HttpException(403, 'Only owner/admin can modify financial records.');
        }
    }
}

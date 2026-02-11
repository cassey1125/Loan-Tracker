<?php

namespace App\Livewire;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Repositories\LoanRepository;
use App\Repositories\PaymentRepository;
use App\Services\Payment\PaymentService;
use Livewire\Component;
use Livewire\WithPagination;

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
        $validated = $this->validate([
            'loan_id' => ['required', 'exists:loans,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:cash,gcash,card'],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = Payment::find($this->editingPaymentId);
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
}

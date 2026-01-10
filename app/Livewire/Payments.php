<?php

namespace App\Livewire;

use App\Http\Requests\StorePaymentRequest;
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
    public $payment_date;
    public $reference_number;
    public $notes;

    public function mount()
    {
        $this->payment_date = date('Y-m-d');
    }

    public function createPayment(PaymentService $service)
    {
        $validated = $this->validate((new StorePaymentRequest())->rules());

        $service->createPayment($validated);

        $this->reset(['loan_id', 'amount', 'reference_number', 'notes']);
        $this->payment_date = date('Y-m-d');

        session()->flash('message', 'Payment recorded successfully.');
    }

    public function render(PaymentRepository $paymentRepository, LoanRepository $loanRepository)
    {
        return view('livewire.payments', [
            'payments' => $paymentRepository->getAll(),
            'activeLoans' => $loanRepository->getActiveLoans(),
        ]);
    }
}

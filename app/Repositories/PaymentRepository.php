<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository
{
    public function getAll(): Collection
    {
        return Payment::with('loan.borrower')->latest()->get();
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function getByLoanId(int $loanId): Collection
    {
        return Payment::where('loan_id', $loanId)->latest()->get();
    }
}

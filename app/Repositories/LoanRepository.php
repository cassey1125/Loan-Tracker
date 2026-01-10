<?php

namespace App\Repositories;

use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Collection;

class LoanRepository
{
    public function save(Loan $loan): Loan
    {
        $loan->save();
        return $loan;
    }

    public function getAll(): Collection
    {
        return Loan::with('borrower')->latest()->get();
    }

    public function getActiveLoans(): Collection
    {
        return Loan::with('borrower')
            ->where('status', '!=', LoanStatus::PAID)
            ->get();
    }

    public function find(int $id): ?Loan
    {
        return Loan::find($id);
    }
}

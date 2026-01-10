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

    public function getPaidLoans($search = null, $dateFrom = null, $dateTo = null): Collection
    {
        $query = Loan::with('borrower')
            ->where('status', LoanStatus::PAID);

        if ($search) {
            $query->whereHas('borrower', function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }

        if ($dateFrom) {
            $query->whereDate('updated_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('updated_at', '<=', $dateTo);
        }

        return $query->latest('updated_at')->get();
    }

    public function find(int $id): ?Loan
    {
        return Loan::find($id);
    }
}

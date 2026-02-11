<?php

namespace App\Repositories;

use App\Enums\LoanStatus;
use App\Models\Borrower;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BorrowerRepository
{
    public function getAll(): LengthAwarePaginator
    {
        return Borrower::latest()->paginate(10);
    }

    public function getBorrowers(
        string $search = '',
        string $status = '',
        string $sortBy = 'name',
        string $sortDirection = 'asc'
    ): LengthAwarePaginator {
        $query = Borrower::query()
            ->withCount('loans')
            ->withSum('loans', 'remaining_balance');

        // Add Next Due Date (Min due date of active loans)
        $query->addSelect(['next_due_date' => \App\Models\Loan::select('due_date')
            ->whereColumn('borrower_id', 'borrowers.id')
            ->where('remaining_balance', '>', 0)
            ->orderBy('due_date', 'asc')
            ->limit(1)
        ]);

        // Search
        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('identification_number', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($status) {
            if ($status === 'paid') {
                $query->having('loans_sum_remaining_balance', '=', 0)
                      ->orHavingNull('loans_sum_remaining_balance');
            } elseif ($status === 'pending') {
                $query->whereHas('loans', function (Builder $q) {
                    $q->where('remaining_balance', '>', 0)
                      ->where('due_date', '>=', now());
                })->whereDoesntHave('loans', function (Builder $q) {
                    $q->where('remaining_balance', '>', 0)
                      ->where('due_date', '<', now());
                });
            } elseif ($status === 'overdue') {
                $query->whereHas('loans', function (Builder $q) {
                    $q->where('remaining_balance', '>', 0)
                      ->where('due_date', '<', now());
                });
            }
        }

        // Sort
        if ($sortBy === 'name') {
            $query->orderBy('first_name', $sortDirection)
                  ->orderBy('last_name', $sortDirection);
        } elseif ($sortBy === 'balance') {
            $query->orderBy('loans_sum_remaining_balance', $sortDirection);
        } elseif ($sortBy === 'due_date') {
            $query->orderBy('next_due_date', $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate(10);
    }

    public function find(int $id): ?Borrower
    {
        return Borrower::find($id);
    }

    public function create(array $data): Borrower
    {
        return Borrower::create($data);
    }

    public function update(Borrower $borrower, array $data): bool
    {
        return $borrower->update($data);
    }

    public function delete(Borrower $borrower): bool
    {
        return $borrower->delete();
    }
}

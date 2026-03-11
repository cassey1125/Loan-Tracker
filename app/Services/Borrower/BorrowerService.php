<?php

namespace App\Services\Borrower;

use App\Models\Borrower;
use App\Repositories\BorrowerRepository;
use Illuminate\Validation\ValidationException;

class BorrowerService
{
    public function __construct(protected BorrowerRepository $repository) {}

    public function createBorrower(array $data): Borrower
    {
        return $this->repository->create($data);
    }

    public function updateBorrower(Borrower $borrower, array $data): bool
    {
        return $this->repository->update($borrower, $data);
    }

    public function deleteBorrower(Borrower $borrower): bool
    {
        if ($borrower->loans()->exists()) {
            throw ValidationException::withMessages([
                'borrower' => 'Cannot delete a borrower who has loan records. Remove or reassign their loans first.',
            ]);
        }

        return $this->repository->delete($borrower);
    }
}

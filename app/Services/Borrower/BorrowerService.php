<?php

namespace App\Services\Borrower;

use App\Models\Borrower;
use App\Repositories\BorrowerRepository;

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
        return $this->repository->delete($borrower);
    }
}

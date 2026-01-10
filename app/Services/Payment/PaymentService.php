<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Repositories\PaymentRepository;

class PaymentService
{
    public function __construct(protected PaymentRepository $repository) {}

    public function createPayment(array $data): Payment
    {
        return $this->repository->create($data);
    }
}

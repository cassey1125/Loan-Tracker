<?php

namespace App\Http\Requests;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loan_id' => ['required', 'exists:loans,id'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    $loan = Loan::find($this->loan_id);
                    if ($loan && $value > $loan->remaining_balance) {
                        $fail("The payment amount cannot exceed the remaining balance of " . number_format($loan->remaining_balance, 2) . ".");
                    }
                },
            ],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

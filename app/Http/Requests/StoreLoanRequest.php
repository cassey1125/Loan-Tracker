<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'borrower_id' => ['required', 'exists:borrowers,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date', 'after:today'],
            'payment_term' => ['required', 'integer', 'min:1'],
        ];
    }
}

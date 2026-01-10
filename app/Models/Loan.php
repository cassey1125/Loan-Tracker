<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_id',
        'amount',
        'interest_rate',
        'due_date',
        'payment_term',
        'interest_amount',
        'total_payable',
        'remaining_balance',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => LoanStatus::class,
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getInvestor1InterestAttribute()
    {
        $rate = $this->interest_rate == 7 ? 5 : 4;
        return $this->amount * ($rate / 100) * $this->payment_term;
    }

    public function getInvestor2InterestAttribute()
    {
        $rate = $this->interest_rate == 7 ? 2 : 1;
        return $this->amount * ($rate / 100) * $this->payment_term;
    }
}

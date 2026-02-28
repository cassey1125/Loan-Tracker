<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    private const INVESTOR_RATE_SPLITS = [
        5 => ['investor1' => 4.0, 'investor2' => 1.0],
        7 => ['investor1' => 5.0, 'investor2' => 2.0],
        10 => ['investor1' => 7.0, 'investor2' => 3.0],
    ];

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

    public function getInvestorRateSplit(): array
    {
        $rate = (int) round((float) $this->interest_rate);

        return self::getInvestorRateSplitFor($rate);
    }

    public static function getInvestorRateSplitFor(int $rate): array
    {
        return self::INVESTOR_RATE_SPLITS[$rate] ?? ['investor1' => 0.0, 'investor2' => 0.0];
    }

    public function getInvestor1RateAttribute(): float
    {
        return $this->getInvestorRateSplit()['investor1'];
    }

    public function getInvestor2RateAttribute(): float
    {
        return $this->getInvestorRateSplit()['investor2'];
    }

    public function getInvestor1InterestAttribute()
    {
        $rate = $this->investor1_rate;
        return $this->amount * ($rate / 100) * $this->payment_term;
    }

    public function getInvestor2InterestAttribute()
    {
        $rate = $this->investor2_rate;
        return $this->amount * ($rate / 100) * $this->payment_term;
    }
}

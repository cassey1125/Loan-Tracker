<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconciliationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_start',
        'period_end',
        'loan_principal_total',
        'loan_interest_total',
        'payments_total',
        'fund_deposits_total',
        'fund_withdrawals_total',
        'calculated_fund_net',
        'mismatch_count',
        'mismatches',
        'generated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'loan_principal_total' => 'decimal:2',
        'loan_interest_total' => 'decimal:2',
        'payments_total' => 'decimal:2',
        'fund_deposits_total' => 'decimal:2',
        'fund_withdrawals_total' => 'decimal:2',
        'calculated_fund_net' => 'decimal:2',
        'mismatches' => 'array',
    ];

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

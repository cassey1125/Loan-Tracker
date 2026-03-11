<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotorRental extends Model
{
    use HasFactory;

    protected $fillable = [
        'motor_name',
        'renter_name',
        'rental_date',
        'rental_days',
        'rental_end_date',
        'notes',
    ];

    protected $casts = [
        'rental_date' => 'date',
        'rental_end_date' => 'date',
        'rental_days' => 'integer',
    ];

    public function getEffectiveRentalEndDateAttribute()
    {
        return $this->rental_end_date ?? $this->rental_date;
    }

    public function getDurationLabelAttribute(): string
    {
        $days = max(1, (int) ($this->rental_days ?? 1));

        return $days === 1 ? '1 day' : $days . ' days';
    }
}

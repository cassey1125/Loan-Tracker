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

    public function getDurationLabelAttribute(): string
    {
        return $this->rental_days === 1 ? '1 day' : $this->rental_days . ' days';
    }
}

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
        'notes',
    ];

    protected $casts = [
        'rental_date' => 'date',
    ];
}

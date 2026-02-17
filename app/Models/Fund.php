<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'amount',
        'type',
        'description',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function reference()
    {
        return $this->morphTo();
    }
}

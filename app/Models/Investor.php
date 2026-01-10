<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function funds()
    {
        return $this->hasMany(Fund::class);
    }

    public function getTotalFundAttribute()
    {
        return $this->funds()->where('type', 'deposit')->sum('amount') 
             - $this->funds()->where('type', 'withdrawal')->sum('amount');
    }
}

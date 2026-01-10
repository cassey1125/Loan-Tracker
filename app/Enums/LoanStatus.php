<?php

namespace App\Enums;

enum LoanStatus: string
{
    case PENDING = 'pending';
    case OVERDUE = 'overdue';
    case PAID = 'paid';
}

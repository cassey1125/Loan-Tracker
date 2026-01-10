<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Component;

class IncomeExpense extends Component
{
    public $startDate;
    public $endDate;

    public function mount()
    {
        // Default to current month
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $income = Payment::whereBetween('payment_date', [$start, $end])->sum('amount');
        
        // Expenses are recorded when loans are released (created_at for now)
        $expenses = Loan::whereBetween('created_at', [$start, $end])->sum('amount');

        $netProfit = $income - $expenses;

        // Get daily income for the chart/list
        $dailyIncome = Payment::whereBetween('payment_date', [$start, $end])
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $paymentsList = Payment::with('loan.borrower')
            ->whereBetween('payment_date', [$start, $end])
            ->orderBy('payment_date', 'desc')
            ->get();
            
        $loansList = Loan::with('borrower')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.income-expense', [
            'totalIncome' => $income,
            'totalExpenses' => $expenses,
            'netProfit' => $netProfit,
            'dailyIncome' => $dailyIncome,
            'paymentsList' => $paymentsList,
            'loansList' => $loansList,
        ]);
    }
}

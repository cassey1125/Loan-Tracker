<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    public $activeTab = 'paid_loans';

    // Filters
    public $startDate;
    public $endDate;
    public $borrowerId;
    public $selectedYear;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->selectedYear = Carbon::now()->year;
    }

    public function updatedActiveTab()
    {
        $this->resetPage();
    }

    public function render()
    {
        $data = [];

        switch ($this->activeTab) {
            case 'paid_loans':
                $data = $this->getPaidLoansData();
                break;
            case 'income_summary':
                $data = $this->getIncomeSummaryData();
                break;
            case 'borrower_history':
                $data = $this->getBorrowerHistoryData();
                break;
            case 'financial_report':
                $data = $this->getFinancialReportData();
                break;
        }

        return view('livewire.reports', [
            'data' => $data,
            'borrowers' => Borrower::orderBy('last_name')->get(),
            'years' => range(Carbon::now()->year, 2023), // Adjust range as needed
        ]);
    }

    public function getPaidLoansData()
    {
        return Loan::where('status', \App\Enums\LoanStatus::PAID)
            ->whereBetween('updated_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->with('borrower')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
    }

    public function getIncomeSummaryData()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $income = Payment::whereBetween('payment_date', [$start, $end])->sum('amount');
        $expenses = Loan::whereBetween('created_at', [$start, $end])->sum('amount');
        
        return [
            'total_income' => $income,
            'total_expenses' => $expenses,
            'net_profit' => $income - $expenses,
            'details' => Payment::with('loan.borrower')
                ->whereBetween('payment_date', [$start, $end])
                ->orderBy('payment_date', 'desc')
                ->paginate(10),
        ];
    }

    public function getBorrowerHistoryData()
    {
        if (!$this->borrowerId) {
            return null;
        }

        return Payment::whereHas('loan', function ($query) {
                $query->where('borrower_id', $this->borrowerId);
            })
            ->with('loan')
            ->orderBy('payment_date', 'desc')
            ->paginate(10);
    }

    public function getFinancialReportData()
    {
        $year = $this->selectedYear;
        $months = range(1, 12);
        $report = [];

        foreach ($months as $month) {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();

            $income = Payment::whereBetween('payment_date', [$start, $end])->sum('amount');
            $expenses = Loan::whereBetween('created_at', [$start, $end])->sum('amount');

            $report[] = [
                'month' => $start->format('F'),
                'income' => $income,
                'expenses' => $expenses,
                'profit' => $income - $expenses,
            ];
        }

        return $report;
    }
}

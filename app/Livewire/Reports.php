<?php

namespace App\Livewire;

use App\Enums\LoanStatus;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'years' => range(Carbon::now()->year, 2023),
        ]);
    }

    public function getPaidLoansData()
    {
        return Loan::where('status', LoanStatus::PAID)
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

    public function getFinancialReportData(): array
    {
        $year = (int) $this->selectedYear;
        $driver = DB::connection()->getDriverName();

        $yearStart = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $yearEnd   = Carbon::createFromDate($year, 12, 31)->endOfDay();

        // Single query for monthly income (payments)
        $monthExpr = $driver === 'sqlite'
            ? "CAST(strftime('%m', payment_date) AS INTEGER)"
            : 'MONTH(payment_date)';

        $incomeByMonth = Payment::query()
            ->selectRaw("{$monthExpr} as month_num, SUM(amount) as total")
            ->whereBetween('payment_date', [$yearStart, $yearEnd])
            ->groupBy('month_num')
            ->pluck('total', 'month_num')
            ->map(fn ($v) => (float) $v);

        // Single query for monthly expenses (loans created)
        $loanMonthExpr = $driver === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';

        $expensesByMonth = Loan::query()
            ->selectRaw("{$loanMonthExpr} as month_num, SUM(amount) as total")
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->groupBy('month_num')
            ->pluck('total', 'month_num')
            ->map(fn ($v) => (float) $v);

        $report = [];
        for ($month = 1; $month <= 12; $month++) {
            $income   = $incomeByMonth[$month] ?? 0.0;
            $expenses = $expensesByMonth[$month] ?? 0.0;
            $report[] = [
                'month'    => Carbon::createFromDate($year, $month, 1)->format('F'),
                'income'   => $income,
                'expenses' => $expenses,
                'profit'   => $income - $expenses,
            ];
        }

        return $report;
    }
}

<?php

namespace App\Livewire;

use App\Models\Fund;
use App\Models\Investor;
use App\Models\Loan;
use App\Models\MotorRental;
use App\Models\Payment;
use App\Enums\LoanStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $chartData = [];
    public $insightsCleared = false;

    public function mount()
    {
    }

    public function clearLendingInsights(): void
    {
        $this->insightsCleared = true;
    }

    public function resetLendingInsights(): void
    {
        $this->insightsCleared = false;
    }

    public function render()
    {
        // 1. Lending Dashboard Stats
        $loans = Loan::all();
        $totalLent = $loans->sum('amount');
        
        $totalExpected = Loan::sum('total_payable');
        $totalRemaining = Loan::sum('remaining_balance');
        $totalCollected = $totalExpected - $totalRemaining;
        
        $paidLoansAmount = Loan::where('status', LoanStatus::PAID)->sum('total_payable');
        $notYetPaidAmount = Loan::where('status', '!=', LoanStatus::PAID)->sum('remaining_balance');
        
        // Investor Earnings (Jo & Rob)
        // Hardcoded logic from Loan model mapping: 5% loans -> Inv1=4%, Inv2=1%. 7% loans -> Inv1=5%, Inv2=2%.
        $loans5 = Loan::where('interest_rate', 5)->get();
        $loans7 = Loan::where('interest_rate', 7)->get();
        
        $earningsJo = 0;
        $earningsRob = 0;

        foreach ($loans5 as $loan) {
            $earningsJo += $loan->investor1_interest;
            $earningsRob += $loan->investor2_interest;
        }
        foreach ($loans7 as $loan) {
            $earningsJo += $loan->investor1_interest;
            $earningsRob += $loan->investor2_interest;
        }

        $totalProfit = $earningsJo + $earningsRob;

        // Total Funds
        $totalDeposits = Fund::where('type', 'deposit')->sum('amount');
        $totalWithdrawals = Fund::where('type', 'withdrawal')->sum('amount');
        $totalInvestorFunds = $totalDeposits - $totalWithdrawals;

        // Loan Status Pie Chart Data
        $statusCounts = Loan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
            
     
        $today = Carbon::today();
        $dueSoonCount = Loan::where('status', LoanStatus::PENDING)
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->count();
            
        $pendingCount = ($statusCounts['pending'] ?? 0) - $dueSoonCount;
        $overdueCount = $statusCounts['overdue'] ?? 0; // Late
        $paidCount = $statusCounts['paid'] ?? 0;
        
        // Prepare Chart Data
        $pieChartData = [
            'labels' => ['Paid', 'Pending', 'Due Soon', 'Late'],
            'data' => [$paidCount, $pendingCount, $dueSoonCount, $overdueCount],
        ];

        // Line Chart Data (Monthly)
        // Cash In (Payments), Total Lent (Loans Created)
        // We need to group by month.
        $months = [];
        $cashInData = [];
        $lentData = [];
        
        // Last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M');
            $months[] = $monthName;
            
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            // Total Lent
            $lent = Loan::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount');
            $lentData[] = $lent;
            
            // Cash In (Funds Deposits) - Use Fund model to track deposits
            $cashIn = Fund::where('type', 'deposit')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');
            $cashInData[] = $cashIn;
        }

        // Recent activity feed
        $recentLoanActivities = Loan::with('borrower')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (Loan $loan) {
                $borrower = $loan->borrower?->full_name ?? 'Unknown borrower';

                return [
                    'timestamp' => $loan->created_at,
                    'title' => 'New loan created',
                    'description' => $borrower . ' - P' . number_format((float) $loan->amount, 2),
                ];
            });

        $recentPaymentActivities = Payment::with('loan.borrower')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (Payment $payment) {
                $borrower = $payment->loan?->borrower?->full_name ?? 'Unknown borrower';

                return [
                    'timestamp' => $payment->created_at,
                    'title' => 'Payment recorded',
                    'description' => $borrower . ' - P' . number_format((float) $payment->amount, 2),
                ];
            });

        $recentFundActivities = Fund::latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (Fund $fund) {
                return [
                    'timestamp' => $fund->created_at,
                    'title' => ucfirst($fund->type) . ' fund entry',
                    'description' => 'P' . number_format((float) $fund->amount, 2),
                ];
            });

        $recentMotorRentalActivities = MotorRental::latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (MotorRental $rental) {
                return [
                    'timestamp' => $rental->created_at,
                    'title' => 'Motor rental added',
                    'description' => $rental->motor_name . ' - ' . $rental->rental_date->format('M d, Y'),
                ];
            });

        $recentActivities = $recentLoanActivities
            ->concat($recentPaymentActivities)
            ->concat($recentFundActivities)
            ->concat($recentMotorRentalActivities)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();

        $lineChartData = [
            'labels' => $months,
            'series' => [
                ['name' => 'Total Lent', 'data' => $lentData],
                ['name' => 'Cash In', 'data' => $cashInData],
            ]
        ];

        if ($this->insightsCleared) {
            $lineChartData = [
                'labels' => [],
                'series' => [
                    ['name' => 'Total Lent', 'data' => []],
                    ['name' => 'Cash In', 'data' => []],
                ],
            ];
        }

        return view('livewire.dashboard', [
            'investors' => Investor::all(), // Kept for other potential uses if any, or can be removed
            'totalLent' => $totalLent,
            'totalCollected' => $totalCollected,
            'totalExpected' => $totalExpected,
            'notYetPaidAmount' => $notYetPaidAmount,
            'earningsJo' => $earningsJo,
            'earningsRob' => $earningsRob,
            'totalProfit' => $totalProfit,
            'totalInvestorFunds' => $totalInvestorFunds,
            'pieChartData' => $pieChartData,
            'lineChartData' => $lineChartData,
            'insightsCleared' => $this->insightsCleared,
            'recentActivities' => $recentActivities,
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Models\Fund;
use App\Models\Loan;
use App\Models\MotorRental;
use App\Models\Payment;
use App\Enums\LoanStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $chartData = [];
    public $insightsCleared = false;
    public $insightsChartVersion = 0;

    public function mount()
    {
    }

    public function clearLendingInsights(): void
    {
        $this->insightsCleared = true;
        $this->insightsChartVersion++;
        $this->dispatch('dashboard-refresh-charts');
    }

    public function resetLendingInsights(): void
    {
        $this->insightsCleared = false;
        $this->insightsChartVersion++;
        $this->dispatch('dashboard-refresh-charts');
    }

    public function render()
    {
        set_time_limit(120);

        // 1. Lending Dashboard Stats
        $loanTotals = Loan::query()
            ->selectRaw('COALESCE(SUM(amount), 0) as total_lent')
            ->selectRaw('COALESCE(SUM(total_payable), 0) as total_expected')
            ->selectRaw('COALESCE(SUM(remaining_balance), 0) as total_remaining')
            ->first();

        $totalLent = (float) ($loanTotals?->total_lent ?? 0);
        $totalExpected = (float) ($loanTotals?->total_expected ?? 0);
        $totalRemaining = (float) ($loanTotals?->total_remaining ?? 0);
        $totalCollected = $totalExpected - $totalRemaining;

        $notYetPaidAmount = Loan::where('status', '!=', LoanStatus::PAID)->sum('remaining_balance');
        
        // Investor Earnings (Jo & Rob)
        $earnings = $this->calculateInvestorEarnings();
        $earningsJo = $earnings['jo'];
        $earningsRob = $earnings['rob'];

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
        [$months, $lentData, $cashInData] = $this->monthlyInsights();

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

    /**
     * @return array{jo: float, rob: float}
     */
    private function calculateInvestorEarnings(): array
    {
        return Cache::remember('dashboard:investor-earnings:v1', now()->addSeconds(20), function (): array {
            $joExpression = "COALESCE(SUM(CASE ROUND(interest_rate) "
                . "WHEN 5 THEN amount * 0.04 * payment_term "
                . "WHEN 7 THEN amount * 0.05 * payment_term "
                . "WHEN 10 THEN amount * 0.07 * payment_term "
                . "ELSE 0 END), 0) as jo_total";

            $robExpression = "COALESCE(SUM(CASE ROUND(interest_rate) "
                . "WHEN 5 THEN amount * 0.01 * payment_term "
                . "WHEN 7 THEN amount * 0.02 * payment_term "
                . "WHEN 10 THEN amount * 0.03 * payment_term "
                . "ELSE 0 END), 0) as rob_total";

            $totals = Loan::query()
                ->selectRaw($joExpression)
                ->selectRaw($robExpression)
                ->first();

            $jo = (float) ($totals?->jo_total ?? 0);
            $rob = (float) ($totals?->rob_total ?? 0);

            return [
                'jo' => round($jo, 2),
                'rob' => round($rob, 2),
            ];
        });
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, float>, 2: array<int, float>}
     */
    private function monthlyInsights(): array
    {
        return Cache::remember('dashboard:monthly-insights:v1', now()->addSeconds(20), function (): array {
            $driver = DB::connection()->getDriverName();
            $loanMonthExpr = $driver === 'sqlite'
                ? "strftime('%Y-%m', created_at)"
                : "DATE_FORMAT(created_at, '%Y-%m')";
            $fundMonthExpr = $driver === 'sqlite'
                ? "strftime('%Y-%m', date)"
                : "DATE_FORMAT(date, '%Y-%m')";

            $monthOrder = [];
            $months = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $key = $date->format('Y-m');
                $monthOrder[] = $key;
                $months[] = $date->format('M');
            }

            $start = Carbon::createFromFormat('Y-m', $monthOrder[0])->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $monthOrder[count($monthOrder) - 1])->endOfMonth();

            $lentByMonth = Loan::query()
                ->selectRaw($loanMonthExpr." as month_key, SUM(amount) as total")
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('month_key')
                ->pluck('total', 'month_key');

            $cashInByMonth = Fund::query()
                ->selectRaw($fundMonthExpr." as month_key, SUM(amount) as total")
                ->where('type', 'deposit')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->groupBy('month_key')
                ->pluck('total', 'month_key');

            $lentData = [];
            $cashInData = [];

            foreach ($monthOrder as $key) {
                $lentData[] = (float) ($lentByMonth[$key] ?? 0);
                $cashInData[] = (float) ($cashInByMonth[$key] ?? 0);
            }

            return [$months, $lentData, $cashInData];
        });
    }
}

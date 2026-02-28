<div class="space-y-6">
    <!-- Top Stats / Lending Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Loan Progress Donut (Simplified as Card for now, can be Chart) -->
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 flex items-center justify-center">
            <div class="text-center">
                <div class="relative w-32 h-32 mx-auto">
                    <!-- Placeholder for Donut Chart -->
                    <div class="absolute inset-0 flex items-center justify-center flex-col">
                        <span class="text-3xl font-bold text-indigo-600">
                            @if($totalExpected > 0)
                                {{ number_format(($totalCollected / $totalExpected) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </span>
                        <span class="text-xs text-gray-500">Paid</span>
                    </div>
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="12" fill="none" />
                        <circle cx="64" cy="64" r="56" stroke="#4f46e5" stroke-width="12" fill="none" stroke-dasharray="351" stroke-dashoffset="{{ 351 - (351 * ($totalExpected > 0 ? ($totalCollected / $totalExpected) : 0)) }}" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 flex flex-col justify-center space-y-4">
            <div>
                <span class="text-sm text-gray-500">Not Yet Paid</span>
                <div class="text-2xl font-bold text-red-600">₱{{ number_format($notYetPaidAmount, 2) }}</div>
            </div>
            <div>
                <span class="text-sm text-gray-500">Total Profit (Jo & Rob)</span>
                <div class="text-2xl font-bold text-green-600">₱{{ number_format($totalProfit, 2) }}</div>
            </div>
        </div>

        <!-- Investor Earnings -->
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 flex flex-col justify-center space-y-4">
            <div>
                <span class="text-sm text-gray-500">Earnings of Jo</span>
                <div class="text-xl font-bold text-blue-600">₱{{ number_format($earningsJo, 2) }}</div>
            </div>
            <div>
                <span class="text-sm text-gray-500">Earnings of Rob</span>
                <div class="text-xl font-bold text-purple-600">₱{{ number_format($earningsRob, 2) }}</div>
            </div>
        </div>

        <!-- Funds Management -->
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 flex flex-col justify-center space-y-4">
            <div>
                <span class="text-sm text-gray-500">Total Funds</span>
                <div class="text-2xl font-bold text-gray-900 break-all">₱{{ number_format($totalInvestorFunds, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Loan Status Pie Chart -->
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Loaner Status</h3>
            <div id="loanStatusChart" class="h-64" data-chart="{{ json_encode($pieChartData) }}"></div>
        </div>

        <!-- Statistics Line Chart -->
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 lg:col-span-2">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="text-lg font-medium text-gray-900">Lending Insights</h3>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="clearLendingInsights"
                        class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100"
                    >
                        Delete
                    </button>
                    <button
                        type="button"
                        wire:click="resetLendingInsights"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </button>
                </div>
            </div>

            @if ($insightsCleared)
                <div class="h-64 flex items-center justify-center text-sm text-gray-500 border border-dashed border-gray-300 rounded-md">
                    Lending insights cleared. Click Reset to load data again.
                </div>
            @else
                <div
                    wire:key="lending-insights-chart-{{ $insightsChartVersion }}"
                    id="lendingInsightsChart"
                    class="h-64"
                    data-chart="{{ json_encode($lineChartData) }}"
                ></div>
            @endif
        </div>
    </div>

    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>

        <div class="space-y-3">
            @forelse ($recentActivities as $activity)
                <div class="border border-gray-100 rounded-md px-4 py-3">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-gray-900">{{ $activity['title'] }}</p>
                        <p class="text-xs text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                        </p>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">{{ $activity['description'] }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">No recent activity found.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="space-y-6">
    <!-- Top Stats / Lending Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Loan Progress Donut (Simplified as Card for now, can be Chart) -->
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-center">
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
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col justify-center space-y-4">
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
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col justify-center space-y-4">
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
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col justify-center space-y-4">
            <div>
                <span class="text-sm text-gray-500">Total Funds</span>
                <div class="text-2xl font-bold text-gray-900 break-all">₱{{ number_format($totalInvestorFunds, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Loan Status Pie Chart -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Loaner Status</h3>
            <div id="loanStatusChart" class="h-64" data-chart="{{ json_encode($pieChartData) }}"></div>
        </div>

        <!-- Statistics Line Chart -->
        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Lending Insights</h3>
            <div id="lendingInsightsChart" class="h-64" data-chart="{{ json_encode($lineChartData) }}"></div>
        </div>
    </div>
</div>

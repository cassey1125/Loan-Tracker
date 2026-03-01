<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Filter by Date</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input wire:model.live="startDate" type="date" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                <input wire:model.live="endDate" type="date" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Cash In (Payments) -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col">
            <span class="text-sm text-gray-500">Total Cash In (Payments)</span>
            <div class="text-2xl font-bold text-green-600">₱{{ number_format($totalIncome, 2) }}</div>
        </div>

        <!-- Total Cash Out (Loans Released) -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col">
            <span class="text-sm text-gray-500">Total Cash Out (Loans Released)</span>
            <div class="text-2xl font-bold text-red-600">₱{{ number_format($totalExpenses, 2) }}</div>
        </div>

        <!-- Net Cash Flow -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col">
            <span class="text-sm text-gray-500">Net Cash Flow</span>
            <div class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                ₱{{ number_format($netProfit, 2) }}
            </div>
        </div>
    </div>

    <!-- Detailed Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Income List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Income (Payments)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($paymentsList as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->loan->borrower->first_name }} {{ $payment->loan->borrower->last_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">₱{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No income found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expense List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Expenses (Loans Released)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($loansList as $loan)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan->borrower->first_name }} {{ $loan->borrower->last_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-red-600">₱{{ number_format($loan->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


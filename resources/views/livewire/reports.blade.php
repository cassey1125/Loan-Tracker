<div class="space-y-6">
    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @foreach([
                'paid_loans' => 'Paid Loans Report',
                'income_summary' => 'Income Summary Report',
                'borrower_history' => 'Borrower Payment History',
                'financial_report' => 'Financial Report (Monthly/Yearly)'
            ] as $key => $label)
                <button 
                    wire:click="$set('activeTab', '{{ $key }}')"
                    class="{{ $activeTab === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Paid Loans Report -->
    @if($activeTab === 'paid_loans')
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Paid Loans Report</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input wire:model.live="startDate" type="date" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input wire:model.live="endDate" type="date" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Paid</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Paid</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data as $loan)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->updated_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan->borrower->first_name }} {{ $loan->borrower->last_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">₱{{ number_format($loan->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">₱{{ number_format($loan->interest_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No paid loans found in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Income Summary Report -->
    @if($activeTab === 'income_summary')
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Income Summary Report</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input wire:model.live="startDate" type="date" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input wire:model.live="endDate" type="date" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-sm text-gray-500">Total Income</span>
                    <div class="text-2xl font-bold text-green-600">₱{{ number_format($data['total_income'], 2) }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-sm text-gray-500">Total Expenses</span>
                    <div class="text-2xl font-bold text-red-600">₱{{ number_format($data['total_expenses'], 2) }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-sm text-gray-500">Net Profit</span>
                    <div class="text-2xl font-bold {{ $data['net_profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        ₱{{ number_format($data['net_profit'], 2) }}
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <h4 class="text-md font-medium text-gray-900 mb-2">Income Details</h4>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data['details'] as $payment)
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
                <div class="mt-4">
                    {{ $data['details']->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Borrower Payment History -->
    @if($activeTab === 'borrower_history')
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Borrower Payment History</h3>

            <div class="mb-6">
                <label for="borrowerId" class="block text-sm font-medium text-gray-700">Select Borrower</label>
                <select wire:model.live="borrowerId" id="borrowerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Select a borrower</option>
                    @foreach($borrowers as $borrower)
                        <option value="{{ $borrower->id }}">{{ $borrower->last_name }}, {{ $borrower->first_name }}</option>
                    @endforeach
                </select>
            </div>

            @if($data)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($data as $payment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $payment->loan_id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($payment->payment_method) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->reference_number ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">₱{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No payment history found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $data->links() }}
                    </div>
                </div>
            @else
                <div class="text-center text-gray-500 py-10">
                    Please select a borrower to view their payment history.
                </div>
            @endif
        </div>
    @endif

    <!-- Financial Report -->
    @if($activeTab === 'financial_report')
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Report (Monthly)</h3>

            <div class="mb-6 w-full md:w-1/3">
                <label for="selectedYear" class="block text-sm font-medium text-gray-700">Select Year</label>
                <select wire:model.live="selectedYear" id="selectedYear" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Income</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Expenses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Profit</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data as $monthData)
                            <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $monthData['month'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">₱{{ number_format($monthData['income'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">₱{{ number_format($monthData['expenses'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $monthData['profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                    ₱{{ number_format($monthData['profit'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">₱{{ number_format(collect($data)->sum('income'), 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">₱{{ number_format(collect($data)->sum('expenses'), 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-blue-600">₱{{ number_format(collect($data)->sum('profit'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>

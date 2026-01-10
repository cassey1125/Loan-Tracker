<div>
    <div class="mb-4">
        <a href="{{ route('borrowers.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Back to Borrowers</a>
    </div>
    
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Profit Analysis: {{ $borrower->full_name }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Detailed breakdown of loan profits for investors.
            </p>
        </div>
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Loan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Interest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Investor 1 Profit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Investor 2 Profit</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $totalInvestor1 = 0;
                            $totalInvestor2 = 0;
                        @endphp
                        @forelse($borrower->loans as $loan)
                            @php
                                $inv1 = $loan->investor1_interest;
                                $inv2 = $loan->investor2_interest;
                                $totalInvestor1 += $inv1;
                                $totalInvestor2 += $inv2;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $loan->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->interest_rate }}%</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->interest_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                                    {{ number_format($inv1, 2) }}
                                    <span class="text-xs text-gray-400">({{ $loan->interest_rate == 7 ? '5%' : '4%' }})</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                                    {{ number_format($inv2, 2) }}
                                    <span class="text-xs text-gray-400">({{ $loan->interest_rate == 7 ? '2%' : '1%' }})</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">No loans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">Totals:</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-700">{{ number_format($totalInvestor1, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-700">{{ number_format($totalInvestor2, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="flex justify-end">
        <a href="{{ route('investor-profit.pdf') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            Download PDF
        </a>
    </div>

    @foreach($rates as $rate)
        @php
            $groupLoans = $loanGroups[$rate] ?? collect();
            $groupSummary = $summary["rate_{$rate}"] ?? null;
        @endphp
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $rate }}% Interest Rate Loans</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Breakdown of profits for loans with {{ $rate }}% interest rate.
                        <br>
                        <span class="font-bold text-indigo-600">Investor 1 ({{ $groupSummary['investor1_rate'] }}%)</span> |
                        <span class="font-bold text-green-600">Investor 2 ({{ $groupSummary['investor2_rate'] }}%)</span>
                    </p>
                    <div class="mt-4 bg-gray-50 p-4 rounded-md">
                        <p class="text-sm font-medium text-gray-500">Total Interest Generated</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($groupSummary['total_interest'], 2) }}</p>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm font-medium text-gray-500">Investor 1 Profit</p>
                            <p class="text-xl font-bold text-indigo-600">{{ number_format($groupSummary['investor1'], 2) }}</p>
                        </div>

                        <div class="mt-2">
                            <p class="text-sm font-medium text-gray-500">Investor 2 Profit</p>
                            <p class="text-xl font-bold text-green-600">{{ number_format($groupSummary['investor2'], 2) }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Loan ID</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Principal</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Term</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Monthly Profit</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Profit</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Return</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-indigo-600">Inv 1 ({{ $groupSummary['investor1_rate'] }}%)</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-green-600">Inv 2 ({{ $groupSummary['investor2_rate'] }}%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($groupLoans as $loan)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#{{ $loan->id }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($loan->amount, 2) }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $loan->payment_term }} mo</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ number_format($loan->amount * ($rate / 100), 2) }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ number_format($loan->interest_amount, 2) }}
                                            <span class="text-xs text-gray-400">({{ $loan->payment_term * $rate }}%)</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($loan->total_payable, 2) }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-indigo-600">{{ number_format($loan->investor1_interest, 2) }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-green-600">{{ number_format($loan->investor2_interest, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No {{ $rate }}% loans found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

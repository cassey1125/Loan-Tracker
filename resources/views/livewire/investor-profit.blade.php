<div class="space-y-6">
    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">5% Interest Rate Loans</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Breakdown of profits for loans with 5% interest rate.
                    <br>
                    <span class="font-bold text-indigo-600">Investor 1 (4%)</span> | 
                    <span class="font-bold text-green-600">Investor 2 (1%)</span>
                </p>
                <div class="mt-4 bg-gray-50 p-4 rounded-md">
                    <p class="text-sm font-medium text-gray-500">Total Interest Generated</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($summary['rate_5']['total_interest'], 2) }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-500">Investor 1 Profit</p>
                        <p class="text-xl font-bold text-indigo-600">{{ number_format($summary['rate_5']['investor1'], 2) }}</p>
                    </div>
                    
                    <div class="mt-2">
                        <p class="text-sm font-medium text-gray-500">Investor 2 Profit</p>
                        <p class="text-xl font-bold text-green-600">{{ number_format($summary['rate_5']['investor2'], 2) }}</p>
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
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Interest</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-indigo-600">Inv 1 (4%)</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-green-600">Inv 2 (1%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($loans5 as $loan)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#{{ $loan->id }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($loan->amount, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $loan->payment_term }} mo</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($loan->interest_amount, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-indigo-600">{{ number_format($loan->investor1_interest, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-green-600">{{ number_format($loan->investor2_interest, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No 5% loans found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">7% Interest Rate Loans</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Breakdown of profits for loans with 7% interest rate.
                    <br>
                    <span class="font-bold text-indigo-600">Investor 1 (5%)</span> | 
                    <span class="font-bold text-green-600">Investor 2 (2%)</span>
                </p>
                <div class="mt-4 bg-gray-50 p-4 rounded-md">
                    <p class="text-sm font-medium text-gray-500">Total Interest Generated</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($summary['rate_7']['total_interest'], 2) }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-500">Investor 1 Profit</p>
                        <p class="text-xl font-bold text-indigo-600">{{ number_format($summary['rate_7']['investor1'], 2) }}</p>
                    </div>
                    
                    <div class="mt-2">
                        <p class="text-sm font-medium text-gray-500">Investor 2 Profit</p>
                        <p class="text-xl font-bold text-green-600">{{ number_format($summary['rate_7']['investor2'], 2) }}</p>
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
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Interest</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-indigo-600">Inv 1 (5%)</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-green-600">Inv 2 (2%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($loans7 as $loan)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#{{ $loan->id }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($loan->amount, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $loan->payment_term }} mo</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($loan->interest_amount, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-indigo-600">{{ number_format($loan->investor1_interest, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-green-600">{{ number_format($loan->investor2_interest, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No 7% loans found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

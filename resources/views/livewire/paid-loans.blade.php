<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 items-end">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search Borrower</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="Enter borrower name..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div>
                <label for="loanId" class="block text-sm font-medium text-gray-700">Loan ID</label>
                <input type="text" wire:model.live.debounce.300ms="loanId" id="loanId" placeholder="Loan ID" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>
            
            <div>
                <label for="dateFrom" class="block text-sm font-medium text-gray-700">Date Fully Paid (From)</label>
                <input type="date" wire:model.live="dateFrom" id="dateFrom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div>
                <label for="dateTo" class="block text-sm font-medium text-gray-700">Date Fully Paid (To)</label>
                <input type="date" wire:model.live="dateTo" id="dateTo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div>
                <label for="startDateFrom" class="block text-sm font-medium text-gray-700">Loan Start Date (From)</label>
                <input type="date" wire:model.live="startDateFrom" id="startDateFrom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div>
                <label for="startDateTo" class="block text-sm font-medium text-gray-700">Loan Start Date (To)</label>
                <input type="date" wire:model.live="startDateTo" id="startDateTo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div class="md:col-span-2 lg:col-span-2 bg-green-50 p-3 rounded-md border border-green-200 text-center flex items-center justify-center h-[62px]">
                <div>
                    <span class="block text-xs font-medium text-green-600 uppercase tracking-wider">Total Income</span>
                    <span class="block text-xl font-bold text-green-700">₱{{ number_format($totalIncome, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan List -->
    <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Paid / Completed Loans
            </h3>
            <span class="text-sm text-gray-500">
                Showing {{ $loans->count() }} records
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Paid</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Started</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Fully Paid</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($loans as $loan)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loan->borrower ? $loan->borrower->full_name : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($loan->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($loan->interest_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">₱{{ number_format($loan->total_payable, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->updated_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ ucfirst($loan->status->value) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No paid loans found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

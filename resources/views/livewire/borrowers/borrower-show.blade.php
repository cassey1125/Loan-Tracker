<div class="space-y-6">
    @if (session()->has('message'))
        <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                {{ $borrower->full_name }}
            </h2>
            <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <span class="material-icons text-gray-400 mr-1.5 text-lg">phone</span>
                    {{ $borrower->phone }}
                </div>
            </div>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('borrowers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to List
            </a>
            @if(auth()->user()?->canManageFinancialRecords())
                <a href="{{ route('borrowers.edit', $borrower) }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Edit
                </a>
            @endif
        </div>
    </div>

    <!-- Details Card -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Borrower Information
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Personal details and address.
            </p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Full name
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $borrower->full_name }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Phone number
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $borrower->phone }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        ID document
                    </dt>
                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                        @if ($borrower->id_document_path)
                            @if(auth()->user()?->canManageFinancialRecords())
                                <a href="{{ route('borrowers.id-document.download', $borrower) }}" class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                                    Download {{ $borrower->id_document_original_name ?? 'ID document' }}
                                </a>
                            @else
                                <span class="text-gray-900">ID document on file</span>
                            @endif
                        @else
                            <span class="text-gray-500">No ID uploaded yet.</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    @if(auth()->user()?->canManageFinancialRecords())
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Upload Borrower ID
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Upload a PDF or clear image of the borrower's government-issued ID.
                </p>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <form wire:submit.prevent="saveIdDocument" class="space-y-4">
                    <div>
                        <label for="idDocument" class="block text-sm font-medium text-gray-700">ID file</label>
                        <input type="file" wire:model="idDocument" id="idDocument" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                        @error('idDocument') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="idDocument" class="mt-2 text-xs text-gray-500">Uploading selected file...</div>
                    </div>

                    @if ($idDocument)
                        <div class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            Selected: {{ $idDocument->getClientOriginalName() }}
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-gray-500">Allowed formats: JPG, PNG, PDF. Maximum size: 5 MB.</p>
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" wire:loading.attr="disabled" wire:target="idDocument,saveIdDocument">
                            {{ $borrower->id_document_path ? 'Replace ID' : 'Upload ID' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Loan History -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Loan History
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    List of all loans associated with this borrower.
                </p>
            </div>
        </div>
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($borrower->loans as $loan)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $loan->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->remaining_balance, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->due_date->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($loan->status === \App\Enums\LoanStatus::PAID) bg-green-100 text-green-800 
                                        @elseif($loan->status === \App\Enums\LoanStatus::OVERDUE) bg-red-100 text-red-800 
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($loan->status->value) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">No loans found for this borrower.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

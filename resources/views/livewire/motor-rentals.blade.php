<div class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium text-gray-900">Rental Calendar</h2>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="previousMonth"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                    Prev
                </button>
                <div class="text-sm font-semibold text-gray-800 min-w-[120px] text-center">
                    {{ $currentMonthLabel }}
                </div>
                <button
                    type="button"
                    wire:click="nextMonth"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                    Next
                </button>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-500 mb-2">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
        </div>

        <div class="grid grid-cols-7 gap-1">
            @foreach ($calendarDays as $day)
                <button
                    type="button"
                    wire:click="selectDate('{{ $day['date'] }}')"
                    class="relative min-h-[74px] rounded-md border p-2 text-left transition
                        {{ $day['inCurrentMonth'] ? 'bg-white border-gray-200' : 'bg-gray-50 border-gray-100 text-gray-400' }}
                        {{ $day['isOccupied'] ? 'bg-green-50 border-green-300 ring-1 ring-green-300' : '' }}
                        {{ $day['isSelected'] ? ($day['isOccupied'] ? 'border-green-600 ring-2 ring-green-500' : 'border-indigo-500 ring-2 ring-indigo-500') : '' }}"
                >
                    <div class="text-sm font-semibold {{ $day['isToday'] ? 'text-indigo-700' : '' }} {{ $day['isOccupied'] ? 'text-green-800' : '' }}">
                        {{ $day['day'] }}
                    </div>
                    @if ($day['isOccupied'])
                        <div class="mt-1 text-[11px] font-medium text-green-700">
                            {{ $day['rentalsCount'] }} active
                        </div>
                    @else
                        <div class="mt-1 text-[11px] text-gray-400">
                            Available
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    @if(auth()->user()?->canManageFinancialRecords())
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ $editingRentalId ? 'Edit Motor Rental' : 'Add Motor Rental' }}
            </h2>

            <form wire:submit.prevent="saveRental" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="motor_name" class="block text-sm font-medium text-gray-700">Motor Name / Unit</label>
                    <input
                        type="text"
                        id="motor_name"
                        wire:model="motor_name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="e.g. Mio 125 - Unit 04"
                    >
                    @error('motor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="renter_name" class="block text-sm font-medium text-gray-700">Renter Name (Optional)</label>
                    <input
                        type="text"
                        id="renter_name"
                        wire:model="renter_name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="e.g. Juan Dela Cruz"
                    >
                    @error('renter_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="rental_date" class="block text-sm font-medium text-gray-700">Rental Date</label>
                    <input
                        type="date"
                        id="rental_date"
                        wire:model="rental_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    @error('rental_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="rental_days" class="block text-sm font-medium text-gray-700">Rental Days</label>
                    <input
                        type="number"
                        id="rental_days"
                        min="1"
                        max="30"
                        wire:model="rental_days"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="e.g. 4"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Ends on {{ 
                            $rental_date
                                ? \Carbon\Carbon::parse($rental_date)->addDays(max(((int) $rental_days) - 1, 0))->format('F d, Y')
                                : '-' 
                        }}
                    </p>
                    @error('rental_days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                    <input
                        type="text"
                        id="notes"
                        wire:model="notes"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Any extra details"
                    >
                    @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                    >
                        {{ $editingRentalId ? 'Update Rental' : 'Save Rental' }}
                    </button>
                    @if ($editingRentalId)
                        <button
                            type="button"
                            wire:click="cancelEdit"
                            class="ml-2 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                    @endif
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Rental Schedule</h2>
                <p class="text-sm text-gray-500">Pick a date to see all rentals active on that day, including multi-day bookings.</p>
                @unless($hasDurationColumns)
                    <p class="text-xs text-amber-600 mt-1">Duration columns are not yet migrated in this environment. Showing single-day schedule mode until migration runs.</p>
                @endunless
            </div>

            <div class="flex items-end gap-2">
                <div>
                    <label for="selectedDate" class="block text-sm font-medium text-gray-700">Filter Date</label>
                    <input
                        type="date"
                        id="selectedDate"
                        wire:model.live="selectedDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                </div>
                <button
                    type="button"
                    wire:click="clearDateFilter"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                    Show All
                </button>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($rentalsByDate as $date => $rentals)
                <div class="border border-gray-200 rounded-md overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700">
                        Starts {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                    </div>

                    @if ($selectedDateLabel)
                        <div class="px-4 py-2 text-xs text-indigo-700 bg-indigo-50 border-t border-indigo-100">
                            Showing rentals active on {{ $selectedDateLabel }}.
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Renter</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($rentals as $rental)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $rental->motor_name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $rental->renter_name ?: '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">
                                            {{ $rental->rental_date->format('M d') }} - {{ $rental->effective_rental_end_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $rental->duration_label }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $rental->notes ?: '-' }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            @if(auth()->user()?->canManageFinancialRecords())
                                                <button
                                                    type="button"
                                                    wire:click="editRental({{ $rental->id }})"
                                                    class="text-indigo-600 hover:text-indigo-800 font-medium"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="requestDelete({{ $rental->id }})"
                                                    class="ml-3 text-red-600 hover:text-red-800 font-medium"
                                                >
                                                    Delete
                                                </button>
                                            @else
                                                <span class="text-xs text-gray-400">View only</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500">No motor rentals found for the selected date.</div>
            @endforelse
        </div>
    </div>
</div>

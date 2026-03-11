<?php

namespace App\Livewire;

use App\Models\MotorRental;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MotorRentals extends Component
{
    public $editingRentalId = null;
    public $motor_name = '';
    public $renter_name = '';
    public $rental_date = '';
    public $rental_days = 1;
    public $notes = '';
    public $selectedDate = '';
    public $currentMonth = '';
    private ?bool $hasDurationColumns = null;

    public function mount(): void
    {
        $today = now()->format('Y-m-d');
        $this->rental_date = $today;
        $this->selectedDate = $today;
        $this->currentMonth = now()->startOfMonth()->format('Y-m-d');
    }

    public function saveRental(): void
    {
        $this->ensureCanManageFinancialRecords();

        $validated = $this->validate([
            'motor_name' => ['required', 'string', 'max:255'],
            'renter_name' => ['nullable', 'string', 'max:255'],
            'rental_date' => ['required', 'date'],
            'rental_days' => ['required', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = [
            'motor_name' => $validated['motor_name'],
            'renter_name' => $validated['renter_name'] ?? null,
            'rental_date' => $validated['rental_date'],
            'notes' => $validated['notes'] ?? null,
        ];

        if ($this->durationColumnsAvailable()) {
            $payload['rental_days'] = (int) $validated['rental_days'];
            $payload['rental_end_date'] = Carbon::parse($validated['rental_date'])
                ->addDays(((int) $validated['rental_days']) - 1)
                ->toDateString();
        }

        if ($this->editingRentalId) {
            $rental = MotorRental::findOrFail($this->editingRentalId);
            $rental->update($payload);
            $this->dispatch('swal:notify', type: 'success', message: 'Motor rental updated.');
        } else {
            MotorRental::create($payload);
            $this->dispatch('swal:notify', type: 'success', message: 'Motor rental added.');
        }

        $this->selectedDate = $this->rental_date;
        $this->resetForm();
    }

    public function editRental(int $id): void
    {
        $this->ensureCanManageFinancialRecords();

        $rental = MotorRental::findOrFail($id);

        $this->editingRentalId = $rental->id;
        $this->motor_name = $rental->motor_name;
        $this->renter_name = $rental->renter_name ?? '';
        $this->rental_date = $rental->rental_date->format('Y-m-d');
        $this->rental_days = (int) ($rental->rental_days ?? 1);
        $this->notes = $rental->notes ?? '';
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function requestDelete(int $id): void
    {
        $this->ensureCanManageFinancialRecords();

        $this->dispatch('swal:confirm-delete', id: $id);
    }

    #[On('motor-rental-delete-confirmed')]
    public function deleteRentalConfirmed(int $id): void
    {
        $this->ensureCanManageFinancialRecords();

        $rental = MotorRental::find($id);

        if (! $rental) {
            return;
        }

        $rental->delete();

        if ($this->editingRentalId === $id) {
            $this->resetForm();
        }

        $this->dispatch('swal:notify', type: 'success', message: 'Motor rental deleted.');
    }

    public function clearDateFilter(): void
    {
        $this->selectedDate = '';
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)
            ->subMonthNoOverflow()
            ->startOfMonth()
            ->format('Y-m-d');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)
            ->addMonthNoOverflow()
            ->startOfMonth()
            ->format('Y-m-d');
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
    }

    private function resetForm(): void
    {
        $defaultDate = $this->selectedDate ?: now()->format('Y-m-d');

        $this->editingRentalId = null;
        $this->motor_name = '';
        $this->renter_name = '';
        $this->rental_days = 1;
        $this->notes = '';
        $this->rental_date = $defaultDate;
    }

    private function ensureCanManageFinancialRecords(): void
    {
        $user = auth()->user();
        if (!$user || !$user->canManageFinancialRecords()) {
            throw new HttpException(403, 'Only owner/admin can modify motor rentals.');
        }
    }

    public function render()
    {
        $hasDurationColumns = $this->durationColumnsAvailable();

        $rentals = MotorRental::query()
            ->when($this->selectedDate, function ($query) use ($hasDurationColumns): void {
                $selectedDate = Carbon::parse($this->selectedDate)->toDateString();

                if ($hasDurationColumns) {
                    $query->whereDate('rental_date', '<=', $selectedDate)
                        ->whereDate('rental_end_date', '>=', $selectedDate);

                    return;
                }

                $query->whereDate('rental_date', '=', $selectedDate);
            })
            ->orderBy('rental_date', 'desc')
            ->orderBy('motor_name')
            ->get();

        $monthStart = Carbon::parse($this->currentMonth)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $occupiedDates = [];
        $monthlyRentals = MotorRental::query();
        if ($hasDurationColumns) {
            $monthlyRentals = $monthlyRentals
                ->whereDate('rental_date', '<=', $monthEnd->toDateString())
                ->whereDate('rental_end_date', '>=', $monthStart->toDateString())
                ->get(['rental_date', 'rental_end_date']);
        } else {
            $monthlyRentals = $monthlyRentals
                ->whereBetween('rental_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->get(['rental_date']);
        }

        foreach ($monthlyRentals as $rental) {
            $rangeStart = $rental->rental_date->copy()->max($monthStart);
            $rangeEnd = ($rental->rental_end_date ?? $rental->rental_date)->copy()->min($monthEnd);

            for ($day = $rangeStart->copy(); $day->lte($rangeEnd); $day->addDay()) {
                $date = $day->toDateString();
                $occupiedDates[$date] = ($occupiedDates[$date] ?? 0) + 1;
            }
        }

        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);
        $calendarDays = [];

        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $date = $day->toDateString();
            $count = (int) ($occupiedDates[$date] ?? 0);

            $calendarDays[] = [
                'date' => $date,
                'day' => $day->day,
                'inCurrentMonth' => $day->month === $monthStart->month,
                'isOccupied' => $count > 0,
                'rentalsCount' => $count,
                'isSelected' => $this->selectedDate === $date,
                'isToday' => $day->isToday(),
            ];
        }

        return view('livewire.motor-rentals', [
            'rentalsByDate' => $rentals->groupBy(function (MotorRental $rental): string {
                return $rental->rental_date->format('Y-m-d');
            }),
            'calendarDays' => $calendarDays,
            'currentMonthLabel' => $monthStart->format('F Y'),
            'selectedDateLabel' => $this->selectedDate ? Carbon::parse($this->selectedDate)->format('F d, Y') : null,
            'hasDurationColumns' => $hasDurationColumns,
        ]);
    }

    private function durationColumnsAvailable(): bool
    {
        if ($this->hasDurationColumns !== null) {
            return $this->hasDurationColumns;
        }

        $this->hasDurationColumns = Schema::hasColumns('motor_rentals', ['rental_days', 'rental_end_date']);

        return $this->hasDurationColumns;
    }
}

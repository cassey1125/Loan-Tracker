<?php

namespace App\Livewire;

use App\Models\Fund;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Funds extends Component
{
    use WithPagination;

    public $editingFundId = null;
    public $amount;
    public $transactionType = 'deposit';
    public $description;
    public $date;

    public function mount()
    {
        $this->date = date('Y-m-d');
    }

    public function saveFund()
    {
        $this->ensureCanManageFinancialRecords();

        $validated = $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'transactionType' => 'required|in:deposit,withdrawal',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        if ($validated['transactionType'] === 'withdrawal') {
            $availableBalance = $this->availableFundBalanceForValidation();
            if ((float) $validated['amount'] > $availableBalance) {
                $this->addError('amount', 'Withdrawal exceeds available funds of ' . number_format($availableBalance, 2) . '.');
                return;
            }
        }

        $payload = [
            'amount' => $validated['amount'],
            'type' => $validated['transactionType'],
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
        ];

        if ($this->editingFundId) {
            $this->ensureCanManageFinancialRecords();

            $fund = Fund::findOrFail($this->editingFundId);
            $fund->update($payload);
            session()->flash('message', 'Fund updated successfully.');
            $this->dispatch('swal:notify', type: 'success', message: 'Fund updated successfully.');
        } else {
            Fund::create($payload);
            session()->flash('message', 'Fund recorded successfully.');
            $this->dispatch('swal:notify', type: 'success', message: 'Fund recorded successfully.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function editFund(int $id): void
    {
        $this->ensureCanManageFinancialRecords();

        $fund = Fund::findOrFail($id);
        if ($this->isSystemGeneratedFund($fund)) {
            session()->flash('message', 'System-generated fund entries from loans/payments cannot be edited.');
            $this->dispatch('swal:notify', type: 'info', message: 'System-generated fund entries from loans/payments cannot be edited.');
            return;
        }

        $this->editingFundId = $fund->id;
        $this->amount = $fund->amount;
        $this->transactionType = $fund->type;
        $this->description = $fund->description;
        $this->date = $fund->date?->format('Y-m-d') ?? $fund->created_at->format('Y-m-d');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function deleteFund(int $id): void
    {
        $this->ensureCanManageFinancialRecords();

        $fund = Fund::findOrFail($id);
        if ($this->isSystemGeneratedFund($fund)) {
            session()->flash('message', 'System-generated fund entries from loans/payments cannot be deleted.');
            $this->dispatch('swal:notify', type: 'info', message: 'System-generated fund entries from loans/payments cannot be deleted.');
            return;
        }
        $fund->delete();

        if ($this->editingFundId === $id) {
            $this->resetForm();
        }

        session()->flash('message', 'Fund deleted successfully.');
        $this->dispatch('swal:notify', type: 'success', message: 'Fund deleted successfully.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->editingFundId = null;
        $this->amount = null;
        $this->transactionType = 'deposit';
        $this->description = null;
        $this->date = date('Y-m-d');
    }

    private function isSystemGeneratedFund(Fund $fund): bool
    {
        return !empty($fund->reference_type) && !empty($fund->reference_id);
    }

    private function availableFundBalanceForValidation(): float
    {
        $deposits = (float) Fund::where('type', 'deposit')->sum('amount');
        $withdrawals = (float) Fund::where('type', 'withdrawal')->sum('amount');
        $available = $deposits - $withdrawals;

        if (!$this->editingFundId) {
            return max(0, round($available, 2));
        }

        $current = Fund::find($this->editingFundId);
        if (!$current) {
            return max(0, round($available, 2));
        }

        if ($current->type === 'deposit') {
            $available -= (float) $current->amount;
        } else {
            $available += (float) $current->amount;
        }

        return max(0, round($available, 2));
    }

    private function ensureCanManageFinancialRecords(): void
    {
        $user = auth()->user();
        if (!$user || !$user->canManageFinancialRecords()) {
            throw new HttpException(403, 'Only owner/admin can modify financial records.');
        }
    }

    public function render()
    {
        return view('livewire.funds', [
            'funds' => Fund::orderBy('date', 'desc')->latest()->paginate(10),
        ]);
    }
}

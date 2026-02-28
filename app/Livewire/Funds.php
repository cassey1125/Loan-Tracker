<?php

namespace App\Livewire;

use App\Models\Fund;
use Livewire\Component;
use Livewire\WithPagination;

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
        $validated = $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'transactionType' => 'required|in:deposit,withdrawal',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $payload = [
            'amount' => $validated['amount'],
            'type' => $validated['transactionType'],
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
        ];

        if ($this->editingFundId) {
            $fund = Fund::findOrFail($this->editingFundId);
            $fund->update($payload);
            session()->flash('message', 'Fund updated successfully.');
        } else {
            Fund::create($payload);
            session()->flash('message', 'Fund recorded successfully.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function editFund(int $id): void
    {
        $fund = Fund::findOrFail($id);

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
        $fund = Fund::findOrFail($id);
        $fund->delete();

        if ($this->editingFundId === $id) {
            $this->resetForm();
        }

        session()->flash('message', 'Fund deleted successfully.');
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

    public function render()
    {
        return view('livewire.funds', [
            'funds' => Fund::orderBy('date', 'desc')->latest()->paginate(10),
        ]);
    }
}

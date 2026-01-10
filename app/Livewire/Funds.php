<?php

namespace App\Livewire;

use App\Models\Fund;
use Livewire\Component;
use Livewire\WithPagination;

class Funds extends Component
{
    use WithPagination;

    public $amount;
    public $type = 'deposit';
    public $description;
    public $date;

    public function mount()
    {
        $this->date = date('Y-m-d');
    }

    public function createFund()
    {
        $validated = $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:deposit,withdrawal',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        Fund::create($validated);

        $this->reset(['amount', 'type', 'description']);
        $this->date = date('Y-m-d');

        session()->flash('message', 'Fund recorded successfully.');
    }

    public function render()
    {
        return view('livewire.funds', [
            'funds' => Fund::orderBy('date', 'desc')->latest()->paginate(10),
        ]);
    }
}

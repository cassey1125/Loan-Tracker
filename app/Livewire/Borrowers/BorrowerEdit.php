<?php

namespace App\Livewire\Borrowers;

use App\Services\Borrower\BorrowerService;
use App\Models\Borrower;
use Livewire\Component;

class BorrowerEdit extends Component
{
    public Borrower $borrower;
    public $first_name;
    public $last_name;
    public $phone;

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower;
        $this->first_name = $borrower->first_name;
        $this->last_name = $borrower->last_name;
        $this->phone = $borrower->phone;
    }

    public function update(BorrowerService $service)
    {
        // Manual validation for simplicity in Livewire context
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $service->updateBorrower($this->borrower, $validated);

        session()->flash('message', 'Borrower updated successfully.');

        return redirect()->route('borrowers.index');
    }

    public function render()
    {
        return view('livewire.borrowers.borrower-edit');
    }
}

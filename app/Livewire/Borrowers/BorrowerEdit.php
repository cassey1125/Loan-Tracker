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
    public $email;
    public $phone;
    public $address;
    public $identification_number;

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower;
        $this->first_name = $borrower->first_name;
        $this->last_name = $borrower->last_name;
        $this->email = $borrower->email;
        $this->phone = $borrower->phone;
        $this->address = $borrower->address;
        $this->identification_number = $borrower->identification_number;
    }

    public function update(BorrowerService $service)
    {
        // Manual validation for simplicity in Livewire context
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:borrowers,email,' . $this->borrower->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'identification_number' => ['nullable', 'string', 'max:50'],
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

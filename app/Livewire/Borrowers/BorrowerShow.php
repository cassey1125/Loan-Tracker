<?php

namespace App\Livewire\Borrowers;

use App\Models\Borrower;
use Livewire\Component;

class BorrowerShow extends Component
{
    public Borrower $borrower;

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower->load('loans');
    }

    public function render()
    {
        return view('livewire.borrowers.borrower-show');
    }
}

<?php

namespace App\Livewire\Borrowers;

use App\Http\Requests\StoreBorrowerRequest;
use App\Services\Borrower\BorrowerService;
use Livewire\Component;

class BorrowerCreate extends Component
{
    public $first_name;
    public $last_name;
    public $phone;

    public function save(BorrowerService $service)
    {
        $user = auth()->user();
        if (!$user || !$user->canManageFinancialRecords()) {
            abort(403, 'Only owner/admin can create borrowers.');
        }

        $validated = $this->validate((new StoreBorrowerRequest())->rules());

        $service->createBorrower($validated);

        session()->flash('message', 'Borrower created successfully.');

        return redirect()->route('borrowers.index');
    }

    public function render()
    {
        return view('livewire.borrowers.borrower-create');
    }
}

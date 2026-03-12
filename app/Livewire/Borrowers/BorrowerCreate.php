<?php

namespace App\Livewire\Borrowers;

use App\Http\Requests\StoreBorrowerRequest;
use App\Services\Borrower\BorrowerService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BorrowerCreate extends Component
{
    use WithFileUploads;

    public $first_name;
    public $last_name;
    public $phone;
    public $idDocument;

    public function save(BorrowerService $service)
    {
        $this->ensureCanManageFinancialRecords();

        $validated = $this->validate(
            array_merge(
                (new StoreBorrowerRequest())->rules(),
                ['idDocument' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']]
            )
        );

        $borrower = $service->createBorrower(
            Arr::except($validated, ['idDocument'])
        );

        if ($this->idDocument) {
            $disk = (string) config('filesystems.borrower_id_disk', 'local');
            $path = $this->idDocument->store("borrower-ids/{$borrower->id}", $disk);
            $borrower->update([
                'id_document_path' => $path,
                'id_document_original_name' => $this->idDocument->getClientOriginalName(),
            ]);
        }

        session()->flash('message', 'Borrower created successfully.');

        return redirect()->route('borrowers.index');
    }

    public function render()
    {
        return view('livewire.borrowers.borrower-create');
    }

    private function ensureCanManageFinancialRecords(): void
    {
        $user = auth()->user();
        if (!$user || !$user->canManageFinancialRecords()) {
            throw new HttpException(403, 'Only owner/admin can create borrowers.');
        }
    }
}

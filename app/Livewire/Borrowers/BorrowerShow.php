<?php

namespace App\Livewire\Borrowers;

use App\Models\Borrower;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BorrowerShow extends Component
{
    use WithFileUploads;

    public Borrower $borrower;
    public $idDocument;

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower->load('loans');
    }

    public function saveIdDocument(): void
    {
        $this->ensureCanManageFinancialRecords();

        $validated = $this->validate([
            'idDocument' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $disk = $this->idDocumentDisk();

        if ($this->borrower->id_document_path) {
            Storage::disk($disk)->delete($this->borrower->id_document_path);
        }

        $path = $validated['idDocument']->store("borrower-ids/{$this->borrower->id}", $disk);

        $this->borrower->update([
            'id_document_path' => $path,
            'id_document_original_name' => $validated['idDocument']->getClientOriginalName(),
        ]);

        $this->borrower = $this->borrower->fresh()->load('loans');
        $this->reset('idDocument');

        session()->flash('message', 'Borrower ID uploaded successfully.');
    }

    public function render()
    {
        return view('livewire.borrowers.borrower-show');
    }

    private function ensureCanManageFinancialRecords(): void
    {
        $user = auth()->user();

        if (!$user || !$user->canManageFinancialRecords()) {
            throw new HttpException(403, 'Only owner/admin can upload borrower IDs.');
        }
    }

    private function idDocumentDisk(): string
    {
        return (string) config('filesystems.borrower_id_disk', 'local');
    }
}

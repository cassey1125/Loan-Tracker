<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BorrowerIdDocumentController extends Controller
{
    public function __invoke(Borrower $borrower): StreamedResponse
    {
        abort_unless($borrower->id_document_path, 404);

        $disk = (string) config('filesystems.borrower_id_disk', 'local');
        abort_unless(Storage::disk($disk)->exists($borrower->id_document_path), 404);

        return Storage::disk($disk)->download(
            $borrower->id_document_path,
            $borrower->id_document_original_name ?: basename($borrower->id_document_path)
        );
    }
}
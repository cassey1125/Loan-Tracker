<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Borrowers\BorrowerCreate;
use App\Models\Borrower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BorrowerIdDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_borrower_with_id_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['role' => UserRole::OWNER]);

        Livewire::actingAs($owner)
            ->test(BorrowerCreate::class)
            ->set('first_name', 'Juan')
            ->set('last_name', 'Dela Cruz')
            ->set('phone', '09123456789')
            ->set('idDocument', UploadedFile::fake()->create('borrower-id.pdf', 128, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('borrowers.index'));

        $borrower = Borrower::query()->latest('id')->firstOrFail();

        $this->assertNotNull($borrower->id_document_path);
        $this->assertSame('borrower-id.pdf', $borrower->id_document_original_name);
        Storage::disk('local')->assertExists($borrower->id_document_path);
    }

    public function test_staff_cannot_create_borrower_with_id_document(): void
    {
        Storage::fake('local');

        $staff = User::factory()->create(['role' => UserRole::STAFF]);

        Livewire::actingAs($staff)
            ->test(BorrowerCreate::class)
            ->set('first_name', 'Maria')
            ->set('last_name', 'Santos')
            ->set('phone', '09999999999')
            ->set('idDocument', UploadedFile::fake()->create('borrower-id.pdf', 128, 'application/pdf'))
            ->call('save')
            ->assertForbidden();
    }

    public function test_borrower_profile_does_not_show_id_upload_options(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $borrower = Borrower::factory()->create();

        $this->actingAs($owner)
            ->get(route('borrowers.show', $borrower))
            ->assertOk()
            ->assertDontSee('Upload Borrower ID')
            ->assertDontSee('Replace ID')
            ->assertDontSee('Upload ID');
    }

    public function test_owner_can_download_borrower_id_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $borrower = Borrower::factory()->create();
        $path = UploadedFile::fake()->create('borrower-id.pdf', 128, 'application/pdf')
            ->store("borrower-ids/{$borrower->id}", 'local');

        $borrower->update([
            'id_document_path' => $path,
            'id_document_original_name' => 'borrower-id.pdf',
        ]);

        $this->actingAs($owner)
            ->get(route('borrowers.id-document.download', $borrower))
            ->assertOk()
            ->assertDownload('borrower-id.pdf');
    }

    public function test_staff_cannot_download_borrower_id_document(): void
    {
        Storage::fake('local');

        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $borrower = Borrower::factory()->create([
            'id_document_path' => 'borrower-ids/1/borrower-id.pdf',
            'id_document_original_name' => 'borrower-id.pdf',
        ]);

        Storage::disk('local')->put($borrower->id_document_path, 'test');

        $this->actingAs($staff)
            ->get(route('borrowers.id-document.download', $borrower))
            ->assertForbidden();
    }
}
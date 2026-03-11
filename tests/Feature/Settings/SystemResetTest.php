<?php

namespace Tests\Feature\Settings;

use App\Enums\UserRole;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class SystemResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(storage_path('app/backups'));
    }

    public function test_owner_can_reset_operational_data_from_settings(): void
    {
        $owner = User::factory()->create([
            'role' => UserRole::OWNER,
            'password' => 'password',
        ]);

        Payment::factory()->count(3)->create();

        $this->assertGreaterThan(0, Borrower::count());
        $this->assertGreaterThan(0, Loan::count());
        $this->assertGreaterThan(0, Payment::count());

        $this->createRecentBackupFile();

        $this->actingAs($owner);

        Livewire::test('settings.reset-system-data-form')
            ->set('confirmation', 'RESET EVERYTHING')
            ->set('password', 'password')
            ->call('resetSystemData')
            ->assertHasNoErrors();

        $this->assertSame(0, Payment::count());
        $this->assertSame(0, Loan::count());
        $this->assertSame(0, Borrower::count());
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_owner_cannot_reset_without_recent_backup(): void
    {
        $owner = User::factory()->create([
            'role' => UserRole::OWNER,
            'password' => 'password',
        ]);

        Payment::factory()->count(2)->create();

        $this->actingAs($owner);

        Livewire::test('settings.reset-system-data-form')
            ->set('confirmation', 'RESET EVERYTHING')
            ->set('password', 'password')
            ->call('resetSystemData')
            ->assertHasErrors(['backup']);

        $this->assertGreaterThan(0, Payment::count());
        $this->assertGreaterThan(0, Loan::count());
        $this->assertGreaterThan(0, Borrower::count());
    }

    public function test_non_owner_cannot_reset_system_data(): void
    {
        $staff = User::factory()->create([
            'role' => UserRole::STAFF,
            'password' => 'password',
        ]);

        $this->actingAs($staff);

        Livewire::test('settings.reset-system-data-form')
            ->set('confirmation', 'RESET EVERYTHING')
            ->set('password', 'password')
            ->call('resetSystemData')
            ->assertForbidden();
    }

    public function test_profile_page_hides_reset_button_for_staff(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);

        $this->actingAs($staff)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Reset all data');
    }

    private function createRecentBackupFile(): void
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $backupPath = $backupDir.DIRECTORY_SEPARATOR.'test-backup.sql';
        File::put($backupPath, '-- backup --');
        touch($backupPath, now()->timestamp);
    }
}

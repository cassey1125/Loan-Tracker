<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_backup_management_page(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);

        $this->actingAs($owner)
            ->get(route('admin.backups.index'))
            ->assertOk();
    }

    public function test_admin_can_view_backup_management_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.backups.index'))
            ->assertOk();
    }

    public function test_staff_cannot_view_backup_management_page(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.backups.index'))
            ->assertForbidden();
    }

    public function test_owner_can_delete_backup_file(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);
        $filename = 'test_delete_backup.sql';
        File::put($backupDir . DIRECTORY_SEPARATOR . $filename, 'dummy');

        $this->actingAs($owner)
            ->delete(route('admin.backups.delete'), ['file' => $filename])
            ->assertRedirect();

        $this->assertFalse(File::exists($backupDir . DIRECTORY_SEPARATOR . $filename));
    }
}

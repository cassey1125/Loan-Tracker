<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Console\Commands\DatabaseBackupCommand;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Tester\CommandTester;
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

    public function test_verify_command_removes_empty_backup_files(): void
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        File::put($backupDir . DIRECTORY_SEPARATOR . 'empty_backup.sql', '');
        File::put($backupDir . DIRECTORY_SEPARATOR . 'valid_backup.sql', 'not empty');

        $exitCode = Artisan::call('db:backup-verify');

        $this->assertSame(0, $exitCode);
        $this->assertFalse(File::exists($backupDir . DIRECTORY_SEPARATOR . 'empty_backup.sql'));
        $this->assertTrue(File::exists($backupDir . DIRECTORY_SEPARATOR . 'valid_backup.sql'));
    }

    public function test_mysql_backup_uses_php_fallback_when_mysqldump_is_missing(): void
    {
        $command = $this->getMockBuilder(DatabaseBackupCommand::class)
            ->onlyMethods(['getConnectionConfig', 'resolveBinaryPath', 'createMySqlFallbackBackup'])
            ->getMock();

        $command->expects($this->once())
            ->method('getConnectionConfig')
            ->willReturn([
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'database' => 'loan_tracker',
                'username' => 'root',
                'password' => '',
            ]);

        $command->expects($this->once())
            ->method('resolveBinaryPath')
            ->willReturn(null);

        $command->expects($this->once())
            ->method('createMySqlFallbackBackup')
            ->willReturn(null);

        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
    }
}

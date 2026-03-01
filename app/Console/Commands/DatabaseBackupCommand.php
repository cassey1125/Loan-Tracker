<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup-daily {--retention=14}';

    protected $description = 'Create a daily database backup file.';

    public function handle(): int
    {
        $defaultConnection = config('database.default');
        $config = config("database.connections.{$defaultConnection}");

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $timestamp = now()->format('Ymd_His');
        $driver = $config['driver'] ?? null;

        if ($driver === 'sqlite') {
            $source = $config['database'];
            $target = "{$backupDir}/backup_{$timestamp}.sqlite";
            File::copy($source, $target);
            $this->info("SQLite backup created: {$target}");
            $this->pruneOldBackups($backupDir, (int) $this->option('retention'));
            return self::SUCCESS;
        }

        if ($driver === 'pgsql') {
            $target = "{$backupDir}/backup_{$timestamp}.sql";
            $command = sprintf(
                'pg_dump -h %s -p %s -U %s -d %s -f "%s"',
                escapeshellarg($config['host']),
                escapeshellarg((string) $config['port']),
                escapeshellarg($config['username']),
                escapeshellarg($config['database']),
                $target
            );

            $result = Process::env(['PGPASSWORD' => $config['password'] ?? ''])->run($command);

            if (!$result->successful()) {
                $this->error('PostgreSQL backup failed: ' . $result->errorOutput());
                return self::FAILURE;
            }

            $this->info("PostgreSQL backup created: {$target}");
            $this->pruneOldBackups($backupDir, (int) $this->option('retention'));
            return self::SUCCESS;
        }

        if ($driver === 'mysql') {
            $target = "{$backupDir}/backup_{$timestamp}.sql";
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s %s > "%s"',
                escapeshellarg($config['host']),
                escapeshellarg((string) $config['port']),
                escapeshellarg($config['username']),
                escapeshellarg($config['database']),
                $target
            );

            $result = Process::env(['MYSQL_PWD' => $config['password'] ?? ''])->run($command);
            if (!$result->successful()) {
                $this->error('MySQL backup failed: ' . $result->errorOutput());
                return self::FAILURE;
            }

            $this->info("MySQL backup created: {$target}");
            $this->pruneOldBackups($backupDir, (int) $this->option('retention'));
            return self::SUCCESS;
        }

        $this->error('Unsupported database driver for automated backup: ' . $driver);
        return self::FAILURE;
    }

    private function pruneOldBackups(string $backupDir, int $retentionDays): void
    {
        $files = File::files($backupDir);
        $threshold = now()->subDays($retentionDays)->getTimestamp();

        foreach ($files as $file) {
            if ($file->getMTime() < $threshold) {
                File::delete($file->getPathname());
            }
        }
    }
}

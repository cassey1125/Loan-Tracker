<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class DatabaseRestoreCommand extends Command
{
    protected $signature = 'db:backup-restore {file : Backup filename in storage/app/backups} {--force : Skip confirmation prompt}';

    protected $description = 'Restore the database from a backup file.';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        $filename = basename((string) $this->argument('file'));
        $source = $backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($source)) {
            $this->error('Backup file not found: ' . $filename);
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('This will overwrite current database state. Continue?', false)) {
            $this->warn('Restore cancelled.');
            return self::FAILURE;
        }

        $defaultConnection = config('database.default');
        $config = config("database.connections.{$defaultConnection}");
        $driver = $config['driver'] ?? null;

        if ($driver === 'sqlite') {
            $databasePath = $config['database'] ?? null;
            if (!$databasePath || $databasePath === ':memory:') {
                $this->error('SQLite restore requires a file-based database path.');
                return self::FAILURE;
            }

            $currentBackup = $backupDir . DIRECTORY_SEPARATOR . 'pre_restore_' . now()->format('Ymd_His') . '.sqlite';
            if (File::exists($databasePath)) {
                File::copy($databasePath, $currentBackup);
            }

            File::copy($source, $databasePath);
            $this->info('SQLite restore completed from ' . $filename);
            return self::SUCCESS;
        }

        if ($driver === 'pgsql') {
            $command = sprintf(
                'psql -h %s -p %s -U %s -d %s -f "%s"',
                escapeshellarg($config['host']),
                escapeshellarg((string) $config['port']),
                escapeshellarg($config['username']),
                escapeshellarg($config['database']),
                $source
            );

            $result = Process::env(['PGPASSWORD' => $config['password'] ?? ''])->run($command);
            if (!$result->successful()) {
                $this->error('PostgreSQL restore failed: ' . $result->errorOutput());
                return self::FAILURE;
            }

            $this->info('PostgreSQL restore completed from ' . $filename);
            return self::SUCCESS;
        }

        if ($driver === 'mysql') {
            $command = sprintf(
                'mysql -h %s -P %s -u %s %s < "%s"',
                escapeshellarg($config['host']),
                escapeshellarg((string) $config['port']),
                escapeshellarg($config['username']),
                escapeshellarg($config['database']),
                $source
            );

            $result = Process::env(['MYSQL_PWD' => $config['password'] ?? ''])->run($command);
            if (!$result->successful()) {
                $this->error('MySQL restore failed: ' . $result->errorOutput());
                return self::FAILURE;
            }

            $this->info('MySQL restore completed from ' . $filename);
            return self::SUCCESS;
        }

        $this->error('Unsupported database driver for restore: ' . $driver);
        return self::FAILURE;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Throwable;

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

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            set_time_limit(300);

            $mysql = $this->resolveBinaryPath('DB_MYSQL_CLIENT_PATH', [
                'mysql',
                'C:\\xampp\\mysql\\bin\\mysql.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.4\\bin\\mysql.exe',
            ]);

            if (!$mysql) {
                $fallbackError = $this->restoreMySqlFallback($source);
                if ($fallbackError === null) {
                    $this->warn('MySQL client executable not found, but PHP fallback restore succeeded.');
                    $this->info('MySQL restore completed from ' . $filename);
                    return self::SUCCESS;
                }

                $this->error('MySQL restore failed: mysql executable not found. Fallback restore failed: ' . $fallbackError);
                return self::FAILURE;
            }

            $sql = File::get($source);
            $result = Process::env(['MYSQL_PWD' => $config['password'] ?? ''])->input($sql)->run([
                $mysql,
                '--protocol=TCP',
                '-h', (string) $config['host'],
                '-P', (string) $config['port'],
                '-u', (string) $config['username'],
                (string) $config['database'],
            ]);

            if ($result->successful()) {
                $this->info('MySQL restore completed from ' . $filename);
                return self::SUCCESS;
            }

            $processError = trim($result->errorOutput() ?: $result->output());
            $fallbackError = $this->restoreMySqlFallback($source);
            if ($fallbackError === null) {
                $this->warn('MySQL client restore failed, but PHP fallback restore succeeded.');
                $this->info('MySQL restore completed from ' . $filename);
                return self::SUCCESS;
            }

            $this->error('MySQL restore failed: ' . $processError . ' Fallback restore failed: ' . $fallbackError);
            return self::FAILURE;
        }

        $this->error('Unsupported database driver for restore: ' . $driver);
        return self::FAILURE;
    }

    private function resolveBinaryPath(string $envKey, array $candidates): ?string
    {
        $configured = env($envKey);
        if (is_string($configured) && $configured !== '' && File::exists($configured)) {
            return $configured;
        }

        foreach ($candidates as $candidate) {
            if (str_contains($candidate, DIRECTORY_SEPARATOR) || str_contains($candidate, ':')) {
                if (File::exists($candidate)) {
                    return $candidate;
                }

                continue;
            }

            $resolved = trim((string) shell_exec('where ' . escapeshellarg($candidate) . ' 2>NUL'));
            if ($resolved !== '') {
                $first = preg_split('/\r\n|\r|\n/', $resolved)[0] ?? null;
                if ($first) {
                    return $first;
                }
            }
        }

        return null;
    }

    private function restoreMySqlFallback(string $source): ?string
    {
        try {
            $sql = File::get($source);
            $statements = $this->splitSqlStatements($sql);

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                foreach ($statements as $statement) {
                    DB::unprepared($statement);
                }
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            return null;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return array<int, string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if ($char === "'" && !$inDoubleQuote && !$inBacktick && $prev !== '\\\\') {
                $inSingleQuote = !$inSingleQuote;
            } elseif ($char === '"' && !$inSingleQuote && !$inBacktick && $prev !== '\\\\') {
                $inDoubleQuote = !$inDoubleQuote;
            } elseif ($char === '`' && !$inSingleQuote && !$inDoubleQuote && $prev !== '\\\\') {
                $inBacktick = !$inBacktick;
            }

            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                $statement = trim($buffer);
                if ($statement !== '' && !$this->isCommentOnlyStatement($statement)) {
                    $statements[] = $statement;
                }

                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $tail = trim($buffer);
        if ($tail !== '' && !$this->isCommentOnlyStatement($tail)) {
            $statements[] = $tail;
        }

        return $statements;
    }

    private function isCommentOnlyStatement(string $statement): bool
    {
        $trimmed = ltrim($statement);

        if (str_starts_with($trimmed, '/*!')) {
            return false;
        }

        return str_starts_with($trimmed, '--')
            || str_starts_with($trimmed, '#')
            || (str_starts_with($trimmed, '/*') && str_ends_with($trimmed, '*/'));
    }
}

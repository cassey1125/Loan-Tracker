<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class VerifyDatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup-verify';

    protected $description = 'Verify latest backup file exists and is readable (restore drill baseline).';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            $this->error('Backup directory does not exist.');
            return self::FAILURE;
        }

        $files = collect(File::files($backupDir))->sortByDesc(fn ($file) => $file->getMTime())->values();
        if ($files->isEmpty()) {
            $this->error('No backup files found.');
            return self::FAILURE;
        }

        $latest = $files->first();
        $size = $latest->getSize();

        if ($size <= 0) {
            $this->error('Latest backup is empty: ' . $latest->getFilename());
            return self::FAILURE;
        }

        $this->info('Latest backup verified: ' . $latest->getFilename() . ' (' . number_format($size) . ' bytes)');

        return self::SUCCESS;
    }
}

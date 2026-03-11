<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class BackupReadinessService
{
    public const DEFAULT_MAX_BACKUP_AGE_HOURS = 24;

    public function hasRecentBackup(int $maxAgeHours = self::DEFAULT_MAX_BACKUP_AGE_HOURS): bool
    {
        $backupDir = storage_path('app/backups');

        if (!File::isDirectory($backupDir)) {
            return false;
        }

        $files = File::files($backupDir);

        if (empty($files)) {
            return false;
        }

        $latestMtime = collect($files)
            ->map(fn ($file) => $file->getMTime())
            ->max();

        if (!$latestMtime) {
            return false;
        }

        $cutoff = Carbon::now()->subHours($maxAgeHours)->timestamp;

        return $latestMtime >= $cutoff;
    }
}

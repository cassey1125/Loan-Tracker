<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class BackupManagementController extends Controller
{
    public function index()
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $files = collect(File::files($backupDir))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            })
            ->values();

        return view('admin.backup-management', [
            'files' => $files,
        ]);
    }

    public function backupNow(): RedirectResponse
    {
        Artisan::call('db:backup-daily');

        if (Artisan::output() && str_contains(strtolower(Artisan::output()), 'failed')) {
            return back()->with('error', trim(Artisan::output()));
        }

        return back()->with('message', 'Backup completed.');
    }

    public function verifyNow(): RedirectResponse
    {
        $exit = Artisan::call('db:backup-verify');

        if ($exit !== 0) {
            return back()->with('error', trim(Artisan::output()));
        }

        return back()->with('message', trim(Artisan::output()));
    }

    public function restore(): RedirectResponse
    {
        $validated = request()->validate([
            'file' => ['required', 'string'],
            'confirm' => ['required', 'in:RESTORE'],
        ]);

        $exit = Artisan::call('db:backup-restore', [
            'file' => basename($validated['file']),
            '--force' => true,
        ]);

        if ($exit !== 0) {
            return back()->with('error', trim(Artisan::output()));
        }

        return back()->with('message', trim(Artisan::output()));
    }

    public function delete(): RedirectResponse
    {
        $validated = request()->validate([
            'file' => ['required', 'string'],
        ]);

        $backupDir = storage_path('app/backups');
        $filename = basename($validated['file']);
        $path = $backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        File::delete($path);

        return back()->with('message', "Deleted backup {$filename}.");
    }
}

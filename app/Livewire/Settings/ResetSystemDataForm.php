<?php

namespace App\Livewire\Settings;

use App\Services\BackupReadinessService;
use App\Services\SystemResetService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Throwable;

class ResetSystemDataForm extends Component
{
    public const CONFIRMATION_PHRASE = 'RESET EVERYTHING';

    public string $password = '';

    public string $confirmation = '';

    public bool $showResetModal = false;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function openResetModal(): void
    {
        $this->showResetModal = true;
        $this->reset('password', 'confirmation');
        $this->resetErrorBag();
        $this->errorMessage = null;
    }

    /**
     * Reset all application data except protected system tables.
     */
    public function resetSystemData(
        SystemResetService $resetService,
        BackupReadinessService $backupReadinessService,
    ): void
    {
        $this->resetErrorBag();
        $this->errorMessage = null;

        $user = Auth::user();

        if (!$user || !$user->canManageUserRoles()) {
            throw new AuthorizationException('Only owner accounts can reset system data.');
        }

        $this->validate([
            'password' => ['required', 'string', 'current_password'],
            'confirmation' => ['required', 'in:'.self::CONFIRMATION_PHRASE],
        ]);

        if (!$backupReadinessService->hasRecentBackup()) {
            $message = 'Create a backup first in Admin > Backups. Latest backup must be within 24 hours.';
            $this->addError('backup', $message);
            $this->errorMessage = $message;
            $this->showResetModal = true;
            $this->dispatch('swal:notify', type: 'warning', message: $message);

            return;
        }

        try {
            $resetService->resetApplicationData();
        } catch (Throwable $e) {
            Log::error('System reset failed.', [
                'user_id' => $user?->id,
                'message' => $e->getMessage(),
            ]);

            $message = 'System reset failed. Please try again or check the logs.';
            $this->addError('general', $message);
            $this->errorMessage = $message;
            $this->showResetModal = true;
            $this->dispatch('swal:notify', type: 'error', message: $message);

            return;
        }

        $this->reset('password', 'confirmation');
        $this->showResetModal = false;
        $this->statusMessage = 'System data reset completed successfully.';
        session()->flash('system-reset-status', 'done');
        $this->dispatch('swal:notify', type: 'success', message: $this->statusMessage);
        $this->dispatch('system-reset-completed');
    }
}

<?php

namespace App\Livewire\Settings;

use App\Enums\UserRole;
use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();

        // Prevent deleting the last owner account — the system would be locked out.
        if ($user->role === UserRole::OWNER) {
            $ownerCount = User::where('role', UserRole::OWNER->value)->count();
            if ($ownerCount <= 1) {
                $this->addError('password', 'You are the last owner. Assign another owner before deleting your account.');
                return;
            }
        }

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}

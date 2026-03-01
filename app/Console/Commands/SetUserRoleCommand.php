<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class SetUserRoleCommand extends Command
{
    protected $signature = 'user:set-role {email} {role}';

    protected $description = 'Set role for a user (owner/admin/staff).';

    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        if (!in_array($role, array_map(fn (UserRole $r) => $r->value, UserRole::cases()), true)) {
            $this->error('Invalid role. Use owner/admin/staff.');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error('User not found.');
            return self::FAILURE;
        }

        $user->role = UserRole::from($role);
        $user->save();

        $this->info("Updated {$email} role to {$role}.");

        return self::SUCCESS;
    }
}

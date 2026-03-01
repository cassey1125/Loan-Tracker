<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    public function index()
    {
        return view('admin.role-management', [
            'users' => User::orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:' . implode(',', array_map(fn (UserRole $r) => $r->value, UserRole::cases()))],
        ]);

        $targetRole = UserRole::from($validated['role']);

        if ($user->id === $request->user()->id && $request->user()->role === UserRole::OWNER && $targetRole !== UserRole::OWNER) {
            return back()->with('error', 'You cannot remove your own owner role.');
        }

        $ownerCount = User::where('role', UserRole::OWNER->value)->count();
        if ($user->role === UserRole::OWNER && $targetRole !== UserRole::OWNER && $ownerCount <= 1) {
            return back()->with('error', 'At least one owner must remain in the system.');
        }

        $user->role = $targetRole;
        $user->save();

        return back()->with('message', "Updated role for {$user->name} to {$targetRole->value}.");
    }
}

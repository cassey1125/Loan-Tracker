<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $filterRole = trim((string) $request->query('role', ''));
        $validRoles = array_map(fn (UserRole $r) => $r->value, UserRole::cases());

        $query = User::query()->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($filterRole !== '' && in_array($filterRole, $validRoles, true)) {
            $query->where('role', $filterRole);
        }

        return view('admin.role-management', [
            'users' => $query->get(),
            'roles' => UserRole::cases(),
            'filters' => [
                'search' => $search,
                'role' => $filterRole,
            ],
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

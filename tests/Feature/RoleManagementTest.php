<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_role_management_page(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);

        $this->actingAs($owner)
            ->get(route('admin.roles.index'))
            ->assertOk();
    }

    public function test_staff_cannot_view_role_management_page(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_owner_can_update_user_role(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $staff = User::factory()->create(['role' => UserRole::STAFF]);

        $this->actingAs($owner)
            ->patch(route('admin.roles.update', $staff), ['role' => UserRole::ADMIN->value])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'role' => UserRole::ADMIN->value,
        ]);
    }

    public function test_owner_can_filter_users_by_search(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $matching = User::factory()->create(['name' => 'Alice Manager', 'email' => 'alice@example.com']);
        $nonMatching = User::factory()->create(['name' => 'Bob Staff', 'email' => 'bob@example.com']);

        $this->actingAs($owner)
            ->get(route('admin.roles.index', ['search' => 'Alice']))
            ->assertOk()
            ->assertSee($matching->name)
            ->assertDontSee($nonMatching->name);
    }

    public function test_owner_can_filter_users_by_role(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $admin = User::factory()->create(['name' => 'Admin User', 'role' => UserRole::ADMIN]);
        $staff = User::factory()->create(['name' => 'Staff User', 'role' => UserRole::STAFF]);

        $this->actingAs($owner)
            ->get(route('admin.roles.index', ['role' => UserRole::ADMIN->value]))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertDontSee($staff->name);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_first_registered_user_becomes_owner(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertEquals(UserRole::OWNER, User::where('email', 'test@example.com')->firstOrFail()->role);
    }

    public function test_subsequent_registered_users_become_staff(): void
    {
        User::factory()->create([
            'role' => UserRole::OWNER,
            'email' => 'owner@example.com',
        ]);

        $response = $this->post(route('register.store'), [
            'name' => 'Jane Staff',
            'email' => 'staff@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertEquals(UserRole::STAFF, User::where('email', 'staff@example.com')->firstOrFail()->role);
    }
}

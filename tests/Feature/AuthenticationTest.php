<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_authenticated_user_with_hashed_password(): void
    {
        $this->post(route('register'), ['name' => 'Robin', 'email' => 'robin@example.test', 'password' => 'my-safe-password', 'password_confirmation' => 'my-safe-password'])->assertRedirect(route('dashboard'));
        $user = User::firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('my-safe-password', $user->password));
        $this->assertDatabaseCount('income_sources', 0);
        $this->get(route('dashboard'))->assertOk()->assertSee('Jouw overzicht begint hier');
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'my-safe-password']);
        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'my-safe-password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_repeated_failed_logins_are_rate_limited(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_reset_notification_and_token_work(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))->assertOk();
            $this->post(route('password.update'), ['token' => $notification->token, 'email' => $user->email,
                'password' => 'a-new-password', 'password_confirmation' => 'a-new-password'])->assertRedirect(route('login'));

            return true;
        });
        $this->assertTrue(Hash::check('a-new-password', $user->fresh()->password));
    }

    public function test_invalid_reset_token_cannot_change_password(): void
    {
        $user = User::factory()->create();
        $this->post(route('password.update'), ['token' => 'invalid', 'email' => $user->email,
            'password' => 'a-new-password', 'password_confirmation' => 'a-new-password'])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_guest_cannot_access_financial_pages(): void
    {
        foreach (['dashboard', 'incomes.index', 'incomes.create', 'expenses.index', 'expenses.create', 'settings.edit'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->post(route('expenses.store'), [])->assertRedirect(route('login'));
    }

    public function test_registration_rejects_duplicate_email_and_short_password(): void
    {
        $user = User::factory()->create();
        $this->post(route('register'), ['name' => 'Robin', 'email' => $user->email, 'password' => 'short', 'password_confirmation' => 'other'])
            ->assertSessionHasErrors(['email', 'password']);
    }
}

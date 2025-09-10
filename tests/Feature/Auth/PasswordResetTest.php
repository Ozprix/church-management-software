<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function reset_password_link_screen_can_be_rendered()
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    /** @test */
    public function reset_password_link_can_be_requested()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(302)
                ->assertSessionHas('status', 'We have emailed your password reset link.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /** @test */
    public function reset_password_screen_can_be_rendered()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->get(route('password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ]));
            $response->assertStatus(200);
            return true;
        });
    }

    /** @test */
    public function password_can_be_reset_with_valid_token()
    {
        Notification::fake();

        $user = User::factory()->create();
        $newPassword = 'new-password';

        // Request password reset link
        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $newPassword) {
            // Reset the password
            // First, get the reset form to set the token in the session
            $resetResponse = $this->get(route('password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ]));
            $resetResponse->assertStatus(200);

            // Get the CSRF token from the reset form
            $token = $this->app['session']->token();
            
            // Now submit the password reset form with CSRF token
            $response = $this->post(route('password.store'), [
                '_token' => $token,
                'token' => $notification->token,
                'email' => $user->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

            $response->assertStatus(302)
                    ->assertSessionHas('status', 'Your password has been reset.');

            // Verify the password was actually changed
            $this->assertTrue(\Illuminate\Support\Facades\Hash::check($newPassword, $user->fresh()->password));

            return true;
        });
    }
}

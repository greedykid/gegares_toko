<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    /** Point the Socialite facade at a fake Google user for one callback. */
    private function fakeGoogleUser(string $id, string $email, string $name = 'Budi'): void
    {
        $abstract = new SocialiteUser;
        $abstract->id = $id;
        $abstract->name = $name;
        $abstract->email = $email;
        $abstract->avatar = 'https://example.com/avatar.png';

        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($abstract);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_a_new_google_user_is_created_and_sent_to_complete_profile(): void
    {
        $this->fakeGoogleUser('google-new-1', 'baru@gmail.com', 'Budi Baru');

        $response = $this->get('/auth/google/callback');

        // The bug: this used to throw on $user->phone (null) and bounce to login.
        $response->assertRedirect(route('settings.complete-profile'));
        $this->assertAuthenticated();

        $user = User::where('email', 'baru@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google-new-1', $user->google_id);
        $this->assertEquals('user', $user->role);
    }

    public function test_an_existing_email_account_is_linked_not_duplicated(): void
    {
        $existing = User::factory()->create([
            'email' => 'lama@gmail.com',
            'phone' => '081234567890',
            'google_id' => null,
        ]);

        $this->fakeGoogleUser('google-link-1', 'lama@gmail.com');

        $response = $this->get('/auth/google/callback');

        // Has a phone already → straight to home, and no duplicate row.
        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertEquals(1, User::where('email', 'lama@gmail.com')->count());
        $this->assertEquals('google-link-1', $existing->fresh()->google_id);
    }

    public function test_an_admin_cannot_sign_in_through_the_public_google_button(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@gegares.shop',
            'role' => 'admin',
            'phone' => '081200000000',
            'google_id' => null,
        ]);

        $this->fakeGoogleUser('google-admin-1', 'admin@gegares.shop', 'Admin');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('google_error');
        $this->assertGuest();

        // The rejection must not have linked Google onto the admin account.
        $this->assertNull($admin->fresh()->google_id);
    }

    public function test_a_returning_google_user_with_a_phone_goes_home(): void
    {
        $user = User::factory()->create([
            'email' => 'return@gmail.com',
            'phone' => '081200001111',
            'google_id' => 'google-return-1',
            'password' => Hash::make('irrelevant'),
        ]);

        $this->fakeGoogleUser('google-return-1', 'return@gmail.com', 'Return User');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user->fresh());
    }
}

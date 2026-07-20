<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCompletionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_phone_can_open_complete_profile_without_a_redirect_loop(): void
    {
        $user = User::factory()->create(['phone' => null]);

        $response = $this->actingAs($user)->get(route('settings.complete-profile'));

        // Must render the page, not redirect back to itself (ERR_TOO_MANY_REDIRECTS).
        $response->assertOk();
    }

    public function test_user_without_phone_is_redirected_to_complete_profile_elsewhere(): void
    {
        $user = User::factory()->create(['phone' => null]);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertRedirect(route('settings.complete-profile'));
    }

    public function test_user_with_phone_is_not_forced_to_complete_profile(): void
    {
        $user = User::factory()->create(['phone' => '081234567890']);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertOk();
    }
}

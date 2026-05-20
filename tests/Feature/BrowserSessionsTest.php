<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowserSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_other_browser_sessions_can_be_logged_out(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->from('/user/profile')->delete(route('other-browser-sessions.destroy', absolute: false), [
            'password' => 'password',
        ]);

        $response->assertStatus(303);
        $response->assertRedirect('/user/profile');
    }
}

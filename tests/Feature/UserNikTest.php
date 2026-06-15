<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNikTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_nik_preserves_leading_zeroes(): void
    {
        $user = User::factory()->create([
            'nik' => '00001',
        ]);

        $this->assertSame('00001', $user->refresh()->nik);
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'nik' => '00001',
        ]);
    }
}

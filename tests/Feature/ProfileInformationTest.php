<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->from('/user/profile')->put(route('user-profile-information.update', absolute: false), [
            'nama' => 'Test Name',
            'username' => $user->username,
            'email' => 'test@example.com',
            'alamat' => null,
            'telepon' => null,
            'tanggal_lahir' => null,
            'posisi' => null,
            'tanggal_masuk' => null,
            'gaji' => null,
            'tipe_gaji' => $user->tipe_gaji ?? 'harian',
            'status' => $user->status ?? 'aktif',
            'role' => $user->role ?? 'Karyawan',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Test Name', $user->fresh()->nama);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }
}

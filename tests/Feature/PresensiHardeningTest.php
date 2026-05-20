<?php

namespace Tests\Feature;

use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_presensi_masuk_uses_server_time_not_client_time(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 12, 8, 0, 0, 'Asia/Jakarta'));

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson(route('presensi.masuk'), [
            'waktu' => '00:00:00', // harus diabaikan
            'lokasi' => '-6.2000000, 106.8166667',
            'latitude' => -6.2,
            'longitude' => 106.8166667,
            'accuracy' => 25,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
        ]);

        $presensi = Presensi::first();
        $this->assertNotNull($presensi);

        $raw = $presensi->getRawOriginal('jam_masuk');
        $this->assertSame('2026-05-12 08:00:00', Carbon::parse($raw)->format('Y-m-d H:i:s'));
        $this->assertNotSame('00:00:00', Carbon::parse($raw)->format('H:i:s'));
    }
}


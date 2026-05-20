<?php

namespace Tests\Feature;

use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_submit_izin_when_presensi_exists_on_same_date(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 18, 8, 0, 0, 'Asia/Jakarta'));

        $user = User::factory()->create();
        $this->actingAs($user);

        Presensi::create([
            'user_id' => $user->user_id,
            'tanggal' => '2026-05-18',
            'jam_masuk' => Carbon::now(config('app.timezone')),
            'lokasi_masuk' => '-6.2000000, 106.8166667',
        ]);

        $response = $this->postJson(route('izin.ajukan'), [
            'tanggal_izin' => '2026-05-18',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'error',
            'message' => 'Anda sudah memiliki aktivitas presensi/lembur pada tanggal ini. Pengajuan izin/cuti tidak tersedia.',
        ]);
    }

    public function test_cannot_submit_izin_when_lembur_exists_on_same_date(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 18, 8, 0, 0, 'Asia/Jakarta'));

        $user = User::factory()->create();
        $this->actingAs($user);

        Lembur::create([
            'user_id' => $user->user_id,
            'tanggal' => '2026-05-18',
            'jam_mulai_lembur' => Carbon::now(config('app.timezone')),
            'lokasi_mulai_lembur' => '-6.2000000, 106.8166667',
        ]);

        $response = $this->postJson(route('izin.ajukan'), [
            'tanggal_izin' => '2026-05-18',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'error',
            'message' => 'Anda sudah memiliki aktivitas presensi/lembur pada tanggal ini. Pengajuan izin/cuti tidak tersedia.',
        ]);
    }

    public function test_eligibility_endpoint_reports_activity_blocking_flags(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 18, 8, 0, 0, 'Asia/Jakarta'));

        $user = User::factory()->create();
        $this->actingAs($user);

        Presensi::create([
            'user_id' => $user->user_id,
            'tanggal' => '2026-05-18',
            'jam_masuk' => Carbon::now(config('app.timezone')),
            'lokasi_masuk' => '-6.2000000, 106.8166667',
        ]);

        $response = $this->getJson(route('izin.eligibility', ['date' => '2026-05-18']));

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.date', '2026-05-18');
        $response->assertJsonPath('data.has_presensi', true);
        $response->assertJsonPath('data.blocked_by_activity', true);
    }
}


<?php

namespace App\Services\Payroll;

use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DailyPayrollService
{
    private const STANDARD_HOURS_PER_DAY = 8;

    /**
     * @return array<string, mixed>
     */
    public function calculateDailyPayroll(
        User $employee,
        string $startDate,
        string $endDate,
        int|float|string|null $dailyWage = null,
    ): array {
        if (strtolower((string) $employee->tipe_gaji) !== 'harian') {
            throw new \InvalidArgumentException('Karyawan yang dipilih bukan karyawan bertipe gaji harian.');
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $dailyWageValue = $dailyWage !== null && $dailyWage !== '' ? (float) $dailyWage : null;

        $attendances = $this->attendanceRows($employee, $start, $end);
        $leaveSummary = $this->leaveSummary($employee, $start, $end);
        $overtimeRows = $this->overtimeRows($employee, $start, $end);

        $validAttendances = $attendances->where('is_counted', true);
        $totalWorkMinutes = (int) $validAttendances->sum('duration_minutes');
        $attendancePayableHours = $this->roundedPayableHours($totalWorkMinutes);

        $validOvertimes = $overtimeRows->where('is_counted', true);
        $totalOvertimeMinutes = (int) $validOvertimes->sum('duration_minutes');
        $overtimePayableHours = $this->roundedPayableHours($totalOvertimeMinutes);
        $overtimeDayEquivalent = intdiv($overtimePayableHours, 4);
        $overtimeRemainingHours = $overtimePayableHours % 4;

        $hourlyWage = $dailyWageValue !== null ? $dailyWageValue / self::STANDARD_HOURS_PER_DAY : null;
        $attendanceGrossTotal = $hourlyWage !== null ? round($attendancePayableHours * $hourlyWage, 2) : null;
        $overtimeDayTotal = $dailyWageValue !== null ? round($overtimeDayEquivalent * $dailyWageValue, 2) : null;
        $overtimeHourTotal = $hourlyWage !== null ? round($overtimeRemainingHours * $hourlyWage, 2) : null;
        $overtimeTotal = $overtimeDayTotal !== null || $overtimeHourTotal !== null
            ? round((float) ($overtimeDayTotal ?? 0) + (float) ($overtimeHourTotal ?? 0), 2)
            : null;
        $grossTotal = $attendanceGrossTotal !== null || $overtimeTotal !== null
            ? round((float) ($attendanceGrossTotal ?? 0) + (float) ($overtimeTotal ?? 0), 2)
            : null;

        $attendanceSummary = [
            'total_present_days' => $attendances->count(),
            'total_valid_attendance_days' => $validAttendances->count(),
            'total_hari_hadir' => $validAttendances->count(),
            'total_jam_kerja' => round($totalWorkMinutes / 60, 2),
            'total_jam_kerja_text' => $this->durationText($totalWorkMinutes),
            'total_menit_kerja' => $totalWorkMinutes,
            'standar_jam_per_hari' => self::STANDARD_HOURS_PER_DAY,
            'total_work_minutes' => $totalWorkMinutes,
            'total_work_hours_label' => $this->durationText($totalWorkMinutes),
            'rounded_payable_hours' => $attendancePayableHours,
            'payable_hours' => $attendancePayableHours,
            'rounding_minutes' => $totalWorkMinutes % 60,
            'rounding_method_label' => $this->roundingMethodLabel(),
            'incomplete_attendance_count' => $attendances->whereIn('status', ['Data tidak lengkap', 'Belum checkout'])->count(),
            'invalid_attendance_count' => $attendances->whereIn('status', ['Data tidak valid', 'Format waktu tidak valid'])->count(),
        ];

        $overtimeSummary = [
            'total_items' => $overtimeRows->count(),
            'total_valid_items' => $validOvertimes->count(),
            'total_overtime_minutes' => $totalOvertimeMinutes,
            'total_overtime_hours_label' => $this->durationText($totalOvertimeMinutes),
            'payable_overtime_hours' => $overtimePayableHours,
            'overtime_day_equivalent' => $overtimeDayEquivalent,
            'overtime_remaining_hours' => $overtimeRemainingHours,
            'overtime_conversion_label' => $this->overtimeConversionLabel($overtimeDayEquivalent, $overtimeRemainingHours),
            'rounding_minutes' => $totalOvertimeMinutes % 60,
            'incomplete_overtime_count' => $overtimeRows->whereIn('status', ['Data tidak lengkap', 'Belum selesai'])->count(),
            'invalid_overtime_count' => $overtimeRows->whereIn('status', ['Data tidak valid', 'Format waktu tidak valid'])->count(),
            'items' => $overtimeRows,
        ];

        return [
            'employee' => [
                'id' => (int) $employee->user_id,
                'name' => $employee->nama,
                'nik' => $employee->nik ?? null,
                'position' => $employee->posisi,
                'wage_type' => 'Harian',
                'user_id' => (int) $employee->user_id,
                'nama' => $employee->nama,
                'posisi' => $employee->posisi,
                'tipe_gaji' => $employee->tipe_gaji,
                'status' => $employee->status,
            ],
            'period' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'label' => $start->translatedFormat('d F Y').' s/d '.$end->translatedFormat('d F Y'),
            ],
            'attendance' => $attendances,
            'attendances' => $attendances,
            'summary' => $attendanceSummary,
            'attendance_summary' => $attendanceSummary,
            'leave_summary' => $leaveSummary,
            'overtime_summary' => $overtimeSummary,
            'payroll' => [
                'gaji_per_hari' => $dailyWageValue,
                'gaji_per_jam' => $hourlyWage !== null ? round($hourlyWage, 2) : null,
                'total_gaji' => $grossTotal,
                'daily_wage' => $dailyWageValue,
                'hourly_wage' => $hourlyWage !== null ? round($hourlyWage, 2) : null,
                'standard_hours_per_day' => self::STANDARD_HOURS_PER_DAY,
                'actual_work_hours_label' => $this->durationText($totalWorkMinutes),
                'payable_hours' => $attendancePayableHours,
                'attendance_payable_hours' => $attendancePayableHours,
                'attendance_gross_total' => $attendanceGrossTotal,
                'overtime_payable_hours' => $overtimePayableHours,
                'overtime_day_equivalent' => $overtimeDayEquivalent,
                'overtime_remaining_hours' => $overtimeRemainingHours,
                'overtime_conversion_label' => $this->overtimeConversionLabel($overtimeDayEquivalent, $overtimeRemainingHours),
                'overtime_day_total' => $overtimeDayTotal,
                'overtime_hour_total' => $overtimeHourTotal,
                'overtime_total' => $overtimeTotal,
                'gross_total' => $grossTotal,
                'is_calculated' => $dailyWageValue !== null,
            ],
        ];
    }

    private function attendanceRows(User $employee, Carbon $start, Carbon $end): Collection
    {
        return Presensi::query()
            ->where('user_id', $employee->user_id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get()
            ->map(fn (Presensi $presensi) => $this->attendanceRow($presensi))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function attendanceRow(Presensi $presensi): array
    {
        $rawStart = $presensi->getRawOriginal('jam_masuk');
        $rawEnd = $presensi->getRawOriginal('jam_keluar');
        $status = 'Lengkap';
        $tone = 'success';
        $durationMinutes = 0;
        $isCounted = false;

        if (! $rawStart) {
            $status = 'Data tidak lengkap';
            $tone = 'danger';
        } elseif (! $rawEnd) {
            $status = 'Belum checkout';
            $tone = 'warning';
        } else {
            try {
                $start = Carbon::parse($rawStart);
                $end = Carbon::parse($rawEnd);

                if ($end->lessThanOrEqualTo($start)) {
                    $status = 'Data tidak valid';
                    $tone = 'danger';
                } else {
                    $durationMinutes = (int) round($start->diffInMinutes($end));
                    $isCounted = true;
                }
            } catch (\Throwable) {
                $status = 'Format waktu tidak valid';
                $tone = 'danger';
            }
        }

        return [
            'id_presensi' => (int) $presensi->id_presensi,
            'tanggal' => optional($presensi->tanggal)->toDateString(),
            'tanggal_human' => optional($presensi->tanggal)->translatedFormat('d F Y'),
            'jam_masuk' => $this->formatTime($rawStart),
            'jam_keluar' => $this->formatTime($rawEnd),
            'duration_minutes' => $durationMinutes,
            'duration_hours' => round($durationMinutes / 60, 2),
            'duration_text' => $isCounted ? $this->durationText($durationMinutes) : '-',
            'duration_label' => $isCounted ? $this->durationText($durationMinutes) : '-',
            'status' => $status,
            'status_tone' => $tone,
            'is_counted' => $isCounted,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function leaveSummary(User $employee, Carbon $start, Carbon $end): array
    {
        $items = Izin::query()
            ->where('user_id', $employee->user_id)
            ->whereBetween('tanggal_izin', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal_izin')
            ->get()
            ->filter(fn (Izin $izin) => $this->isApprovedRecord($izin, 'izin'))
            ->flatMap(function (Izin $izin) {
                try {
                    $date = Carbon::parse($izin->tanggal_izin);
                } catch (\Throwable $exception) {
                    report($exception);

                    return [];
                }

                return [[
                    'id_izin' => (int) $izin->id_izin,
                    'date' => $date->toDateString(),
                    'date_label' => $date->translatedFormat('d F Y'),
                    'type' => $this->firstFilledAttribute($izin, ['jenis_izin', 'tipe_izin', 'jenis', 'tipe', 'kategori']) ?? 'Izin',
                    'reason' => $this->firstFilledAttribute($izin, ['keterangan', 'alasan', 'reason']) ?? '-',
                    'status' => 'Disetujui',
                    'days' => 1,
                ]];
            })
            ->values();

        return [
            'total_items' => $items->count(),
            'total_days' => (int) $items->sum('days'),
            'items' => $items,
        ];
    }

    private function overtimeRows(User $employee, Carbon $start, Carbon $end): Collection
    {
        return Lembur::query()
            ->where('user_id', $employee->user_id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai_lembur')
            ->get()
            ->filter(fn (Lembur $lembur) => $this->isApprovedRecord($lembur, 'lembur'))
            ->map(fn (Lembur $lembur) => $this->overtimeRow($lembur))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function overtimeRow(Lembur $lembur): array
    {
        $rawStart = $lembur->getRawOriginal('jam_mulai_lembur');
        $rawEnd = $lembur->getRawOriginal('jam_pulang_lembur');
        $status = 'Lengkap';
        $tone = 'success';
        $durationMinutes = 0;
        $isCounted = false;

        if (! $rawStart) {
            $status = 'Data tidak lengkap';
            $tone = 'danger';
        } elseif (! $rawEnd) {
            $status = 'Belum selesai';
            $tone = 'warning';
        } else {
            try {
                $start = Carbon::parse($rawStart);
                $end = Carbon::parse($rawEnd);

                if ($end->lessThanOrEqualTo($start)) {
                    $status = 'Data tidak valid';
                    $tone = 'danger';
                } else {
                    $durationMinutes = (int) round($start->diffInMinutes($end));
                    $isCounted = true;
                }
            } catch (\Throwable) {
                $status = 'Format waktu tidak valid';
                $tone = 'danger';
            }
        }

        return [
            'id_lembur' => (int) $lembur->id_lembur,
            'tanggal' => optional($lembur->tanggal)->toDateString(),
            'tanggal_human' => optional($lembur->tanggal)->translatedFormat('d F Y'),
            'jam_mulai' => $this->formatTime($rawStart),
            'jam_selesai' => $this->formatTime($rawEnd),
            'duration_minutes' => $durationMinutes,
            'duration_hours' => round($durationMinutes / 60, 2),
            'duration_label' => $isCounted ? $this->durationText($durationMinutes) : '-',
            'duration_text' => $isCounted ? $this->durationText($durationMinutes) : '-',
            'status' => $status,
            'status_tone' => $tone,
            'is_counted' => $isCounted,
        ];
    }

    private function isApprovedRecord(object $model, string $table): bool
    {
        $status = $this->firstExistingFilledAttribute($model, $table, ['status_approval', 'approval_status', 'status']);

        if ($status === null) {
            return ! Schema::hasColumn($table, 'approved_at') || filled($model->approved_at ?? null);
        }

        return in_array(strtolower($status), ['approved', 'approve', 'disetujui', 'accepted', 'diterima'], true);
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function firstExistingFilledAttribute(object $model, string $table, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! Schema::hasColumn($table, $key)) {
                continue;
            }

            $value = $model->{$key} ?? null;
            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            return trim((string) $value);
        }

        return null;
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function firstFilledAttribute(object $model, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $model->{$key} ?? null;

            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            return trim((string) $value);
        }

        return null;
    }

    private function durationText(int $minutes): string
    {
        $hours = intdiv(max(0, $minutes), 60);
        $remainingMinutes = max(0, $minutes) % 60;

        return "{$hours} jam {$remainingMinutes} menit";
    }

    private function roundedPayableHours(int $totalMinutes): int
    {
        $hours = intdiv(max(0, $totalMinutes), 60);
        $remainingMinutes = max(0, $totalMinutes) % 60;

        return $remainingMinutes > 30 ? $hours + 1 : $hours;
    }

    private function overtimeConversionLabel(int $dayEquivalent, int $remainingHours): string
    {
        return "{$dayEquivalent} hari kerja + {$remainingHours} jam";
    }

    private function formatTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    private function roundingMethodLabel(): string
    {
        return '1-30 menit dibulatkan ke bawah, 31-59 menit dibulatkan ke atas';
    }
}

<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\CalculateDailyPayrollRequest;
use App\Models\User;
use App\Services\Payroll\DailyPayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class DailyPayrollController extends Controller
{
    public function index(): Response
    {
        return $this->render();
    }

    public function calculate(CalculateDailyPayrollRequest $request, DailyPayrollService $payroll): Response|RedirectResponse
    {
        $validated = $request->validated();

        try {
            $employee = $this->findDailyEmployee((int) $validated['karyawan_id']);
            if (! $employee) {
                return back()->withErrors([
                    'karyawan_id' => 'Karyawan yang dipilih bukan karyawan bertipe gaji harian.',
                ])->withInput();
            }

            return $this->render([
                'filters' => [
                    'karyawan_id' => (int) $employee->user_id,
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'tanggal_selesai' => $validated['tanggal_selesai'],
                    'gaji_per_hari' => $validated['gaji_per_hari'] ?? null,
                    'overtime_hourly_rate' => $validated['overtime_hourly_rate'] ?? null,
                    'mode' => $validated['mode'],
                ],
                'selectedEmployee' => $this->employeePayload($employee),
                'result' => $payroll->calculateDailyPayroll(
                    $employee,
                    $validated['tanggal_mulai'],
                    $validated['tanggal_selesai'],
                    $validated['gaji_per_hari'] ?? null,
                    $validated['overtime_hourly_rate'] ?? null,
                ),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors([
                'payroll' => $exception->getMessage(),
            ])->withInput();
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'payroll' => 'Terjadi kesalahan saat memproses penggajian. Silakan coba lagi.',
            ])->withInput();
        }
    }

    public function print(CalculateDailyPayrollRequest $request, DailyPayrollService $payroll): View|RedirectResponse
    {
        $validated = $request->validated();

        try {
            $employee = $this->findDailyEmployee((int) $validated['karyawan_id']);
            if (! $employee) {
                return back()->withErrors([
                    'karyawan_id' => 'Karyawan yang dipilih bukan karyawan bertipe gaji harian.',
                ])->withInput();
            }

            $result = $payroll->calculateDailyPayroll(
                $employee,
                $validated['tanggal_mulai'],
                $validated['tanggal_selesai'],
                $validated['gaji_per_hari'],
                $validated['overtime_hourly_rate'] ?? null,
            );

            $view = view('payroll.daily-print', [
                'employee' => $this->employeePayload($employee),
                'result' => $result,
                'period' => $result['period'],
                'summary' => $result['summary'],
                'payroll' => $result['payroll'],
                'attendances' => $result['attendances'],
                'leaveSummary' => $result['leave_summary'],
                'overtimeSummary' => $result['overtime_summary'],
                'generatedAt' => now(config('app.timezone'))->translatedFormat('d F Y H:i'),
            ]);

            $view->render();

            return $view;
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors([
                'payroll' => $exception->getMessage(),
            ])->withInput();
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'payroll' => 'Slip gaji gagal dicetak. Silakan coba lagi.',
            ])->withInput();
        }
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function render(array $props = []): Response
    {
        return Inertia::render('Payroll/DailyCalculator', array_merge([
            'employees' => User::query()
                ->where('tipe_gaji', 'harian')
                ->orderBy('nama')
                ->get(['user_id', 'nik', 'nama', 'posisi', 'tipe_gaji', 'status'])
                ->map(fn (User $user) => $this->employeePayload($user))
                ->values(),
            'filters' => [
                'karyawan_id' => null,
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
                'gaji_per_hari' => null,
                'overtime_hourly_rate' => null,
                'mode' => 'preview',
            ],
            'selectedEmployee' => null,
            'result' => null,
        ], $props));
    }

    /**
     * @return array<string, mixed>
     */
    private function employeePayload(User $user): array
    {
        return [
            'user_id' => (int) $user->user_id,
            'nik' => $user->nik ?? null,
            'nama' => $user->nama,
            'posisi' => $user->posisi,
            'tipe_gaji' => $user->tipe_gaji,
            'status' => $user->status,
        ];
    }

    private function findDailyEmployee(int $employeeId): ?User
    {
        return User::query()
            ->where('user_id', $employeeId)
            ->where('tipe_gaji', 'harian')
            ->first();
    }
}

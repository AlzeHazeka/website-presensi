<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\CalculateDailyPayrollRequest;
use App\Models\User;
use App\Services\Payroll\DailyPayrollService;
use Illuminate\Http\JsonResponse;
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

    public function calculate(CalculateDailyPayrollRequest $request, DailyPayrollService $payroll): JsonResponse
    {
        $validated = $request->validated();

        try {
            $employee = $this->findDailyEmployee((int) $validated['karyawan_id']);
            if (! $employee) {
                return response()->json([
                    'message' => 'Karyawan yang dipilih bukan karyawan bertipe gaji harian.',
                    'errors' => [
                        'karyawan_id' => ['Karyawan yang dipilih bukan karyawan bertipe gaji harian.'],
                    ],
                ], 422);
            }

            $result = $payroll->calculateDailyPayroll(
                $employee,
                $validated['tanggal_mulai'],
                $validated['tanggal_selesai'],
                $validated['gaji_per_hari'] ?? null,
            );

            return response()->json([
                'message' => 'Perhitungan gaji berhasil diproses.',
                'employee' => $this->employeePayload($employee),
                'filters' => [
                    'karyawan_id' => (int) $employee->user_id,
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'tanggal_selesai' => $validated['tanggal_selesai'],
                    'gaji_per_hari' => $validated['gaji_per_hari'] ?? null,
                    'mode' => $validated['mode'],
                ],
                'attendances' => $result['attendances'],
                'attendance' => $result['attendance'],
                'summary' => $result['summary'],
                'attendance_summary' => $result['attendance_summary'],
                'leave_summary' => $result['leave_summary'],
                'overtime_summary' => $result['overtime_summary'],
                'payroll' => $result['payroll'],
                'period' => $result['period'],
                'result' => $result,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [
                    'payroll' => [$exception->getMessage()],
                ],
            ], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses penggajian. Silakan coba lagi.',
                'errors' => [
                    'payroll' => ['Terjadi kesalahan saat memproses penggajian. Silakan coba lagi.'],
                ],
            ], 500);
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

<?php

namespace App\Http\Requests\Payroll;

use App\Support\Permissions;
use App\Support\RoleAccess;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculateDailyPayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user?->can(Permissions::PAYROLL_VIEW)
            && RoleAccess::userHasAnyRole($user, Roles::adminRoles());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'karyawan_id' => [
                'required',
                Rule::exists('users', 'user_id')->where(fn ($query) => $query->where('tipe_gaji', 'harian')),
            ],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'mode' => ['required', Rule::in(['preview', 'calculate'])],
            'gaji_per_hari' => ['nullable', 'required_if:mode,calculate', 'numeric', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'karyawan_id.required' => 'Silakan pilih karyawan terlebih dahulu.',
            'karyawan_id.exists' => 'Karyawan yang dipilih bukan karyawan bertipe gaji harian.',
            'tanggal_mulai.required' => 'Silakan pilih tanggal mulai.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Silakan pilih tanggal selesai.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'mode.required' => 'Mode perhitungan tidak valid.',
            'mode.in' => 'Mode perhitungan tidak valid.',
            'gaji_per_hari.required_if' => 'Silakan isi nominal gaji per hari terlebih dahulu.',
            'gaji_per_hari.numeric' => 'Nominal gaji per hari harus berupa angka.',
            'gaji_per_hari.min' => 'Gaji per hari harus lebih dari 0.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'karyawan_id' => 'karyawan',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_selesai' => 'tanggal selesai',
            'gaji_per_hari' => 'gaji per hari',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Anda tidak memiliki akses ke fitur penggajian.');
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'alamat' => null,
            'telepon' => null,
            'posisi' => null,
            'tanggal_lahir' => null,
            'tanggal_masuk' => null,
            'gaji' => null,
            'tipe_gaji' => 'harian',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'status' => 'aktif',
            'role' => 'Karyawan',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => 'Admin',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => 'tidak aktif',
        ]);
    }
}

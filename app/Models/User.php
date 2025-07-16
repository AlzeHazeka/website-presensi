<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $primaryKey = 'user_id'; // Menetapkan user_id sebagai primary key
    public $incrementing = true; // Pastikan ini diatur ke true jika menggunakan auto-increment
    protected $keyType = 'int'; // Tipe kunci

    // Beri tahu Laravel bahwa primary key untuk autentikasi adalah user_id
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    public function getAuthIdentifier()
    {
        return $this->user_id;
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'user_id', 'user_id');
    }

    public function izin()
    {
        return $this->hasMany(Izin::class, 'user_id', 'user_id');
    }

    public function lembur()
    {
        return $this->hasMany(Lembur::class, 'user_id', 'user_id');
    }


    protected $fillable = [
        'nama',
        'username',
        'email',
        'password',
        'alamat',
        'posisi',
        'telepon',
        'tanggal_lahir',
        'tanggal_masuk',
        'gaji',
        'tipe_gaji',
        'status',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_masuk' => 'date', // Jika Anda ingin mengcast tanggal masuk
            'gaji' => 'integer', // Jika Anda ingin mengcast gaji
        ];
    }
}

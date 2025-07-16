<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';
    protected $primaryKey = 'id_presensi'; // Pastikan Laravel tahu primary key-nya
    public $incrementing = true;
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'lokasi_masuk',
        'jam_keluar',
        'lokasi_keluar',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime', // Casting ke datetime agar mudah digunakan
        'jam_keluar' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id'); // Relasi ke user_id
    }

    // Mutator & Accessor untuk menampilkan jam masuk & keluar dalam format yang lebih enak dibaca
    public function getJamMasukAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

    public function getJamKeluarAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }
}

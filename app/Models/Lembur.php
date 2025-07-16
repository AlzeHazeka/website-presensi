<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lembur';
    protected $primaryKey = 'id_lembur';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_mulai_lembur',
        'lokasi_mulai_lembur',
        'jam_pulang_lembur',
        'lokasi_pulang_lembur',
    ];

    protected $casts = [
    'tanggal' => 'date',
    'jam_mulai_lembur' => 'datetime',
    'jam_pulang_lembur' => 'datetime',
    ];


    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getJamMulaiLemburAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

    public function getJamPulangLemburAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

}

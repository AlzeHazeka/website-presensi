<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'lat_mulai_lembur',
        'lng_mulai_lembur',
        'accuracy_mulai_lembur',
        'ip_mulai_lembur',
        'ua_mulai_lembur',
        'jam_pulang_lembur',
        'lokasi_pulang_lembur',
        'lat_selesai_lembur',
        'lng_selesai_lembur',
        'accuracy_selesai_lembur',
        'ip_selesai_lembur',
        'ua_selesai_lembur',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai_lembur' => 'datetime',
        'jam_pulang_lembur' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

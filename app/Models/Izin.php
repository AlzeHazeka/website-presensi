<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izin';
    protected $primaryKey = 'id_izin';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'tanggal_pengajuan',
        'tanggal_izin',
    ];

    protected $casts = [
    'tanggal_izin' => 'date',
    'tanggal_pengajuan' => 'datetime',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getJamIzinAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

}

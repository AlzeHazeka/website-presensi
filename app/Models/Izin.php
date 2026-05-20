<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_izin' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

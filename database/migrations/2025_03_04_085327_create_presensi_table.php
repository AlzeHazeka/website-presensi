<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->increments('id_presensi');
            $table->unsignedInteger('user_id'); // Ubah ke bigInteger agar seragam dengan bigIncrements
            $table->date('tanggal'); // Tanggal presensi
            $table->dateTime('jam_masuk')->nullable(); // Ubah ke dateTime
            $table->string('lokasi_masuk')->nullable(); // Lokasi saat presensi masuk
            $table->dateTime('jam_keluar')->nullable(); // Ubah ke dateTime
            $table->string('lokasi_keluar')->nullable(); // Lokasi saat presensi keluar
            $table->timestamps(); // created_at & updated_at

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};

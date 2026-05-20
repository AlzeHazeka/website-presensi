<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('izin', function (Blueprint $table) {
            $table->increments('id_izin');
            $table->unsignedInteger('user_id');
            $table->dateTime('tanggal_pengajuan');
            $table->date('tanggal_izin');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        Schema::create('lembur', function (Blueprint $table) {
            $table->increments('id_lembur');
            $table->unsignedInteger('user_id');
            $table->date('tanggal');
            $table->dateTime('jam_mulai_lembur')->nullable();
            $table->string('lokasi_mulai_lembur')->nullable();
            $table->dateTime('jam_pulang_lembur')->nullable();
            $table->string('lokasi_pulang_lembur')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembur');
        Schema::dropIfExists('izin');
    }
};


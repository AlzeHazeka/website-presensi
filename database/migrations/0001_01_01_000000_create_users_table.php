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
        // Membuat tabel user
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id'); // Kolom id_karyawan sebagai increments (INT auto-increment)
            $table->string('nama'); // Kolom nama
            $table->string('email')->unique(); // Kolom email harus unik
            $table->string('username', 20); // Kolom username
            $table->string('alamat', 100) ->nullable(); // Kolom alamat
            $table->string('telepon', 15) ->nullable(); // Kolom No HP
            $table->string('posisi', 50) ->nullable(); // Kolom posisi
            $table->date('tanggal_lahir') ->nullable(); // Kolom tanggal masuk
            $table->date('tanggal_masuk') ->nullable(); // Kolom tanggal masuk
            $table->integer('gaji') ->nullable(); // Kolom gaji
            $table->enum('tipe_gaji', ['harian', 'bulanan']) ->default('harian'); // Kolom tipe gaji
            $table->string('password'); // Kolom password
            $table->rememberToken(); // Kolom untuk "ingat saya"
            $table->string('profile_photo_path', 2048)->nullable(); // Kolom untuk link foto profil
            $table->enum('status', ['aktif', 'tidak aktif','menunggu'])->default('menunggu'); // Kolom status karyawan
            $table->enum('role', ['Admin', 'Karyawan'])->default('Karyawan'); // Kolom role karyawan
            $table->timestamps(); // Menambahkan kolom created_at dan updated_at
        });

        // Membuat tabel password_reset_tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Menggunakan email sebagai primary key
            $table->string('token'); // Kolom token untuk reset password
            $table->timestamp('created_at')->nullable(); // Waktu token dibuat
        });

        // Membuat tabel sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // ID session
            $table->unsignedInteger('user_id')->nullable()->index(); // Mengganti user_id dengan id_karyawan
            $table->string('ip_address', 45)->nullable(); // IP address
            $table->text('user_agent')->nullable(); // User agent
            $table->longText('payload'); // Payload
            $table->integer('last_activity')->index(); // Waktu aktivitas terakhir

            // Menambahkan foreign key constraint
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

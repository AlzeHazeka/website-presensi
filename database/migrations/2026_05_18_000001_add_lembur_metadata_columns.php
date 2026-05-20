<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lembur', function (Blueprint $table) {
            $table->decimal('lat_mulai_lembur', 10, 7)->nullable()->after('lokasi_mulai_lembur');
            $table->decimal('lng_mulai_lembur', 10, 7)->nullable()->after('lat_mulai_lembur');
            $table->unsignedInteger('accuracy_mulai_lembur')->nullable()->after('lng_mulai_lembur');
            $table->string('ip_mulai_lembur', 45)->nullable()->after('accuracy_mulai_lembur');
            $table->string('ua_mulai_lembur', 255)->nullable()->after('ip_mulai_lembur');

            // "selesai" = pulang lembur (naming operasional)
            $table->decimal('lat_selesai_lembur', 10, 7)->nullable()->after('lokasi_pulang_lembur');
            $table->decimal('lng_selesai_lembur', 10, 7)->nullable()->after('lat_selesai_lembur');
            $table->unsignedInteger('accuracy_selesai_lembur')->nullable()->after('lng_selesai_lembur');
            $table->string('ip_selesai_lembur', 45)->nullable()->after('accuracy_selesai_lembur');
            $table->string('ua_selesai_lembur', 255)->nullable()->after('ip_selesai_lembur');
        });
    }

    public function down(): void
    {
        Schema::table('lembur', function (Blueprint $table) {
            $table->dropColumn([
                'lat_mulai_lembur',
                'lng_mulai_lembur',
                'accuracy_mulai_lembur',
                'ip_mulai_lembur',
                'ua_mulai_lembur',
                'lat_selesai_lembur',
                'lng_selesai_lembur',
                'accuracy_selesai_lembur',
                'ip_selesai_lembur',
                'ua_selesai_lembur',
            ]);
        });
    }
};


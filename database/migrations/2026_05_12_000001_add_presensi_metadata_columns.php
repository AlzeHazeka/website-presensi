<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->decimal('lat_masuk', 10, 7)->nullable()->after('lokasi_masuk');
            $table->decimal('lng_masuk', 10, 7)->nullable()->after('lat_masuk');
            $table->unsignedInteger('accuracy_masuk')->nullable()->after('lng_masuk');
            $table->string('ip_masuk', 45)->nullable()->after('accuracy_masuk');
            $table->string('ua_masuk', 255)->nullable()->after('ip_masuk');

            $table->decimal('lat_keluar', 10, 7)->nullable()->after('lokasi_keluar');
            $table->decimal('lng_keluar', 10, 7)->nullable()->after('lat_keluar');
            $table->unsignedInteger('accuracy_keluar')->nullable()->after('lng_keluar');
            $table->string('ip_keluar', 45)->nullable()->after('accuracy_keluar');
            $table->string('ua_keluar', 255)->nullable()->after('ip_keluar');
        });
    }

    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropColumn([
                'lat_masuk',
                'lng_masuk',
                'accuracy_masuk',
                'ip_masuk',
                'ua_masuk',
                'lat_keluar',
                'lng_keluar',
                'accuracy_keluar',
                'ip_keluar',
                'ua_keluar',
            ]);
        });
    }
};


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
        Schema::table('visa_forms', function (Blueprint $table) {
            $table->string('jenis_kelamin', 10)->nullable()->after('tanggal_lahir');
            $table->string('status_kawin', 30)->nullable()->after('jenis_kelamin');
            $table->string('jenis_kelamin_penanggung_jawab', 10)->nullable()->after('nama_penanggung_jawab');
        });

        Schema::table('_history_visa_forms', function (Blueprint $table) {
            $table->string('jenis_kelamin', 10)->nullable()->after('tanggal_lahir');
            $table->string('status_kawin', 30)->nullable()->after('jenis_kelamin');
            $table->string('jenis_kelamin_penanggung_jawab', 10)->nullable()->after('nama_penanggung_jawab');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_forms', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'status_kawin', 'jenis_kelamin_penanggung_jawab']);
        });

        Schema::table('_history_visa_forms', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'status_kawin', 'jenis_kelamin_penanggung_jawab']);
        });
    }
};


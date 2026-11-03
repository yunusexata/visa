<?php

use App\Models\AlurPencairan\AlurPencairanDetail;
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

            $table->dateTime('tanggal_cancel')->default(null)->nullable();
            $table->dateTime('tanggal_selesai')->default(null)->nullable();
        });
        Schema::table('_history_visa_forms', function (Blueprint $table) {
            $table->dateTime('tanggal_cancel')->default(null)->nullable();
            $table->dateTime('tanggal_selesai')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_forms', function (Blueprint $table) {
            $table->dropColumn('tanggal_cancel');
            $table->dropColumn('tanggal_selesai');
        });
        Schema::table('_history_visa_forms', function (Blueprint $table) {
            $table->dropColumn('tanggal_cancel');
            $table->dropColumn('tanggal_selesai');
        });
    }
};

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
        Schema::create('visa_form_attachments', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_visa_form_attachments', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('visa_form_attachments');
        Schema::dropIfExists('_history_visa_form_attachments');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index(['visa_form_id', 'jenis_dokumen'], 'visa_form_a_form_jenis_idx');
            $table->index('jenis_dokumen', 'visa_form_a_jenis_idx');
            $table->index('status', 'visa_form_a_status_idx');
            $table->foreign('visa_form_id')->references('id')->on('visa_forms')->cascadeOnDelete();
        }

        $table->unsignedBigInteger('visa_form_id');

        // Kategori Dokumen (coe, e_ktln, ktp, paspor, tiket, pas_photo, persyaratan_pengurusan_visa)
        $table->string('jenis_dokumen', 50);

        // Informasi Berkas (Bahasa Indonesia)
        $table->string('nama_file_asli');
        $table->string('nama_file_tersimpan');
        $table->string('path');
        $table->string('disk', 30)->nullable()->default('public');
        $table->string('extension', 10);
        $table->string('mime_type', 50);
        $table->unsignedBigInteger('file_size'); // Dalam bytes

        // Status & Catatan Audit
        $table->string('status', 30)->default('stored')->index();
        $table->text('catatan')->nullable();
        $table->unsignedInteger('urutan')->default(1);

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};

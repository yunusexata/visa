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
        Schema::create('visa_forms', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_visa_forms', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('visa_forms');
        Schema::dropIfExists('_history_visa_forms');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
            $table->unsignedBigInteger('visa_form_link_id')->nullable();
            $table->string('nomor_registrasi', 50)->nullable();
        } else {
            $table->index('nomor_registrasi', 'visa_forms_nomor_registrasi_idx');
            $table->index('status', 'visa_forms_status_idx');
            $table->index('pic_code', 'visa_forms_pic_code_idx');
            $table->index('nama_lengkap', 'visa_forms_nama_lengkap_idx');
            $table->foreignId('visa_form_link_id')->nullable()->constrained('visa_form_links')->nullOnDelete();
            $table->string('nomor_registrasi', 50)->nullable()->unique();
        }

        $table->string('status', 30)->default('DIAJUKAN')->index();
        $table->string('pic_code', 50)->nullable()->index();

        // Step 1: Identitas Diri Pemohon
        $table->string('nama_lengkap');
        $table->string('tempat_lahir');
        $table->date('tanggal_lahir');
        $table->string('nik_ktp', 16)->index();
        $table->text('alamat_indonesia');
        $table->string('kode_pos_indonesia', 10)->nullable();
        $table->string('telepon_indonesia', 25);
        $table->string('email_aktif');

        // Step 2: Data di Paspor
        $table->string('nomor_paspor', 25)->index();
        $table->string('kantor_paspor');
        $table->date('tanggal_pengeluaran_paspor');
        $table->date('tanggal_habis_berlaku_paspor');

        // Step 3: Certificate of Eligibility (COE)
        $table->string('nomor_coe', 50)->index();
        $table->string('status_kependudukan_jepang');
        $table->string('lama_stay_jepang', 30);

        // Step 4: Rencana Kedatangan & Kaisha di Jepang
        $table->date('tanggal_kedatangan');
        $table->string('bandara_tujuan');
        $table->string('bandara_lainnya')->nullable();
        $table->string('maskapai');
        $table->string('nama_kaisha');
        $table->text('alamat_jepang');
        $table->string('kode_pos_jepang', 15)->nullable();
        $table->string('telepon_jepang', 25);
        $table->string('pernah_kerja_jepang', 10)->default('tidak');
        $table->string('periode_kerja_mulai', 30)->nullable();
        $table->string('periode_kerja_selesai', 30)->nullable();
        $table->string('nama_penanggung_jawab');
        $table->string('alamat_penanggung_jawab');
        $table->string('telepon_penanggung_jawab', 25);

        // Step 5: Pernyataan, Riwayat & Audit
        $table->boolean('pernyataan_persetujuan')->default(false);
        $table->timestamp('tanggal_diajukan')->nullable();
        $table->timestamp('tanggal_verifikasi')->nullable();
        $table->text('catatan')->nullable();

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};

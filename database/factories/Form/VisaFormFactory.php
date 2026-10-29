<?php

namespace Database\Factories\Form;

use App\Enums\Form\VisaFormStatus;
use App\Models\Form\VisaForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisaForm>
 */
class VisaFormFactory extends Factory
{
    protected $model = VisaForm::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visa_form_link_id' => null,
            'nomor_registrasi' => 'VISA-'.date('Ymd').'-'.fake()->unique()->numerify('####'),
            'status' => VisaFormStatus::DIAJUKAN,
            'pic_code' => fake()->bothify('PIC-###'),

            // Step 1: Identitas Diri
            'nama_lengkap' => fake()->name(),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-20 years'),
            'nik_ktp' => fake()->numerify('3578############'),
            'alamat_indonesia' => fake()->address(),
            'kode_pos_indonesia' => fake()->numerify('#####'),
            'telepon_indonesia' => fake()->numerify('0812########'),
            'email_aktif' => fake()->unique()->safeEmail(),

            // Step 2: Data Paspor
            'nomor_paspor' => fake()->bothify('C#######'),
            'kantor_paspor' => 'KANIM SURABAYA',
            'tanggal_pengeluaran_paspor' => fake()->date('Y-m-d', '-1 year'),
            'tanggal_habis_berlaku_paspor' => fake()->date('Y-m-d', '+4 years'),

            // Step 3: COE
            'nomor_coe' => fake()->bothify('COE-2026-######'),
            'status_kependudukan_jepang' => 'Specified Skilled Worker (SSW / Tokutei Ginou)',
            'lama_stay_jepang' => '1 Tahun',

            // Step 4: Rencana Kedatangan & Kaisha di Jepang
            'tanggal_kedatangan' => fake()->date('Y-m-d', '+1 month'),
            'bandara_tujuan' => 'Haneda (HND)',
            'bandara_lainnya' => null,
            'maskapai' => 'Garuda Indonesia',
            'nama_kaisha' => fake()->company().' CO., LTD.',
            'alamat_jepang' => 'Tokyo-to, Shinjuku-ku, 1-2-3',
            'kode_pos_jepang' => '100-0001',
            'telepon_jepang' => fake()->numerify('080########'),
            'pernah_kerja_jepang' => 'tidak',
            'periode_kerja_mulai' => null,
            'periode_kerja_selesai' => null,
            'nama_penanggung_jawab' => fake()->name(),
            'alamat_penanggung_jawab' => 'Tokyo-to, Shinjuku-ku, 1-2-3',
            'telepon_penanggung_jawab' => fake()->numerify('080########'),

            // Step 5: Persetujuan
            'pernyataan_persetujuan' => true,
            'tanggal_diajukan' => now(),
            'tanggal_verifikasi' => null,
            'catatan' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}

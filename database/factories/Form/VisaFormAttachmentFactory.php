<?php

namespace Database\Factories\Form;

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisaFormAttachment>
 */
class VisaFormAttachmentFactory extends Factory
{
    protected $model = VisaFormAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visa_form_id' => VisaForm::factory(),
            'jenis_dokumen' => fake()->randomElement(VisaAttachmentType::cases()),
            'nama_file_asli' => fake()->lexify('berkas_????.jpg'),
            'nama_file_tersimpan' => fake()->uuid().'.jpg',
            'jalur_file' => 'visa-documents/'.fake()->uuid().'.jpg',
            'disk' => 'public',
            'ekstensi' => 'jpg',
            'tipe_mime' => 'image/jpeg',
            'ukuran_file' => fake()->numberBetween(100000, 2000000),
            'status' => VisaAttachmenStatus::STATUS_STORED,
            'catatan' => null,
            'urutan' => 1,
        ];
    }
}

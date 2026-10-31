<?php

namespace App\Services\Form;

use App\Models\Form\VisaForm;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;

class VisaJapanPdfService
{
    /**
     * Generate the filled Japan Visa Application Form as binary PDF string.
     */
    public function generate(VisaForm $visaForm): string
    {
        $templatePath = storage_path('app/private/template_form_visa.pdf');

        if (! file_exists($templatePath)) {
            throw new \RuntimeException("Template PDF formulir visa Jepang tidak ditemukan di: {$templatePath}");
        }

        $pdf = new Fpdi;
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        $pageCount = $pdf->setSourceFile($templatePath);

        // --- HALAMAN 1 ---
        $tplId1 = $pdf->importPage(1);
        $size1 = $pdf->getTemplateSize($tplId1);
        $pdf->AddPage($size1['orientation'], [$size1['width'], $size1['height']]);
        $pdf->useTemplate($tplId1, 0, 0, $size1['width'], $size1['height']);

        $this->renderPage1($pdf, $visaForm);

        // --- HALAMAN 2 ---
        if ($pageCount >= 2) {
            $tplId2 = $pdf->importPage(2);
            $size2 = $pdf->getTemplateSize($tplId2);
            $pdf->AddPage($size2['orientation'], [$size2['width'], $size2['height']]);
            $pdf->useTemplate($tplId2, 0, 0, $size2['width'], $size2['height']);

            $this->renderPage2($pdf, $visaForm);
        }

        return $pdf->Output('S');
    }

    /**
     * Render data pemohon ke Halaman 1 formulir visa Jepang.
     */
    protected function renderPage1(Fpdi $pdf, VisaForm $visaForm): void
    {
        $pdf->SetTextColor(0, 0, 0);

        // Pemisahan Nama Lengkap menjadi Surname dan Given Names
        $nameParts = preg_split('/\s+/', trim((string) $visaForm->nama_lengkap)) ?: [];
        if (count($nameParts) > 1) {
            $surname = array_pop($nameParts);
            $givenNames = implode(' ', $nameParts);
        } else {
            $surname = $nameParts[0] ?? '';
            $givenNames = '';
        }

        // 1. Surname & Given Names
        $this->writeField($pdf, 75, 75, strtoupper($surname), 9);
        $this->writeField($pdf, 90, 83, strtoupper($givenNames), 9);

        // 2. Tanggal Lahir & Tempat Lahir
        $dob = $visaForm->tanggal_lahir instanceof Carbon
            ? $visaForm->tanggal_lahir->format('d - m - Y')
            : ($visaForm->tanggal_lahir ? Carbon::parse($visaForm->tanggal_lahir)->format('d - m - Y') : '');
        $this->writeField($pdf, 40, 100, $dob, 9);

        $pob = trim(strtoupper((string) $visaForm->tempat_lahir));
        if ($pob && ! str_contains($pob, 'INDONESIA')) {
            $pob .= ', INDONESIA';
        }
        $this->writeField($pdf, 95, 102, $pob, 8.5);

        // 3. Jenis Kelamin (Sex)
        $isFemale = false;
        if (! empty($visaForm->jenis_kelamin)) {
            $isFemale = strtoupper((string) $visaForm->jenis_kelamin) === 'P';
        } elseif (strlen((string) $visaForm->nik_ktp) >= 8) {
            $dayDigits = (int) substr((string) $visaForm->nik_ktp, 6, 2);
            if ($dayDigits > 40) {
                $isFemale = true;
            }
        }
        if ($isFemale) {
            $this->writeCheckbox($pdf, 60, 106.5); // Female [X]
        } else {
            $this->writeCheckbox($pdf, 41, 106.5); // Male [X]
        }

        // 4. Status Perkawinan (Marital Status)
        $statusKawin = strtolower((string) ($visaForm->status_kawin ?? ''));
        if ($statusKawin === 'belum kawin' || $statusKawin === 'single') {
            $this->writeCheckbox($pdf, 115, 106.5); // Single [X]
        } elseif ($statusKawin === 'janda/duda' || $statusKawin === 'widowed') {
            $this->writeCheckbox($pdf, 156, 106.5); // Widowed [X]
        } elseif ($statusKawin === 'cerai' || $statusKawin === 'divorced') {
            $this->writeCheckbox($pdf, 176, 106.5); // Divorced [X]
        } else {
            $this->writeCheckbox($pdf, 137, 106.5); // Married [X] (default)
        }

        // 5. Kewarganegaraan (Nationality)
        $this->writeField($pdf, 65, 115, 'INDONESIA', 9);
        $this->writeField($pdf, 120, 120.5, 'INDONESIA', 9);

        // 6. ID No. (NIK KTP)
        $this->writeField($pdf, 85, 128, (string) $visaForm->nik_ktp, 9);

        // 7. Passport Type & Details
        $this->writeCheckbox($pdf, 122, 133); // Ordinary [X]
        $this->writeField($pdf, 158, 133, strtoupper((string) $visaForm->nomor_paspor), 9);

        // Tempat Penerbitan & Tanggal Terbit
        $passportOffice = strtoupper((string) $visaForm->kantor_paspor);
        $this->writeField($pdf, 48, 144, $passportOffice, 8.5);

        $issueDate = $visaForm->tanggal_pengeluaran_paspor instanceof Carbon
            ? $visaForm->tanggal_pengeluaran_paspor->format('d - m - Y')
            : ($visaForm->tanggal_pengeluaran_paspor ? Carbon::parse($visaForm->tanggal_pengeluaran_paspor)->format('d - m - Y') : '');
        $this->writeField($pdf, 162, 142, $issueDate, 8.5);

        // Instansi Penerbit & Tanggal Habis Berlaku
        $authority = $passportOffice ? "IMIGRASI {$passportOffice}" : 'KANTOR IMIGRASI';
        $this->writeField($pdf, 55, 149, $authority, 8.5);

        $expiryDate = $visaForm->tanggal_habis_berlaku_paspor instanceof Carbon
            ? $visaForm->tanggal_habis_berlaku_paspor->format('d - m - Y')
            : ($visaForm->tanggal_habis_berlaku_paspor ? Carbon::parse($visaForm->tanggal_habis_berlaku_paspor)->format('d - m - Y') : '');
        $this->writeField($pdf, 170, 149, $expiryDate, 8.5);

        // 8. COE & Status Tinggal
        $this->writeField($pdf, 65, 156, strtoupper((string) $visaForm->nomor_coe), 9);

        $statusJepang = strtoupper((string) ($visaForm->status_kependudukan_jepang ?: 'SPECIFIED SKILLED WORKER (i)'));
        $this->writeField($pdf, 95, 163, $statusJepang, 8.5);

        // 9. Lama Tinggal & Tanggal Kedatangan
        $lamaStay = strtoupper((string) ($visaForm->lama_stay_jepang ?: '1 YEAR'));
        $this->writeField($pdf, 75, 171, $lamaStay, 9);

        $arrivalDate = $visaForm->tanggal_kedatangan instanceof Carbon
            ? $visaForm->tanggal_kedatangan->format('d/m/Y')
            : ($visaForm->tanggal_kedatangan ? Carbon::parse($visaForm->tanggal_kedatangan)->format('d/m/Y') : '');
        $this->writeField($pdf, 152, 171, $arrivalDate, 9);

        // 10. Bandara Tujuan & Maskapai
        $bandara = strtoupper((string) ($visaForm->bandara_tujuan ?: 'HANEDA AIRPORT'));
        $this->writeField($pdf, 65, 178, $bandara, 8.5);

        $maskapai = strtoupper((string) ($visaForm->maskapai ?: 'GARUDA INDONESIA'));
        $this->writeField($pdf, 152, 178, $maskapai, 8.5);

        // 11. Data Kaisha / Hotel / Tempat Tinggal di Jepang
        $kaisha = strtoupper((string) $visaForm->nama_kaisha);
        $this->writeField($pdf, 45, 193, $kaisha, 8.5);

        $telpJepang = (string) $visaForm->telepon_jepang;
        $this->writeField($pdf, 152, 193, $telpJepang, 8.5);

        $alamatJepang = strtoupper((string) $visaForm->alamat_jepang);
        $this->writeField($pdf, 45, 204, $alamatJepang, 8);

        // 12. Riwayat Tinggal Sebelumnya di Jepang
        if (strtolower((string) $visaForm->pernah_kerja_jepang) === 'ya') {
            $riwayat = trim($visaForm->periode_kerja_mulai.' - '.$visaForm->periode_kerja_selesai);
            $this->writeField($pdf, 93, 218, $riwayat ?: 'YES', 8.5);
        } else {
            $this->writeField($pdf, 93, 218, 'NO', 8.5);
        }

        // 13. Alamat Domisili Indonesia, Telepon, & Email
        $alamatIndo = strtoupper((string) $visaForm->alamat_indonesia);
        if ($visaForm->kode_pos_indonesia) {
            $alamatIndo .= ', '.$visaForm->kode_pos_indonesia;
        }
        $this->writeField($pdf, 45, 224, $alamatIndo, 8);

        $telpIndo = (string) $visaForm->telepon_indonesia;
        $this->writeField($pdf, 95, 236, $telpIndo, 9);

        $email = strtoupper((string) $visaForm->email_aktif);
        $this->writeField($pdf, 45, 244, $email, 8.5);

        // 14. Pekerjaan Sekarang
        $this->writeField($pdf, 95, 251, 'UNEMPLOYEE', 8.5);
    }

    /**
     * Render data penjamin & pernyataan ke Halaman 2 formulir visa Jepang.
     */
    protected function renderPage2(Fpdi $pdf, VisaForm $visaForm): void
    {
        $pdf->SetTextColor(0, 0, 0);

        // 1. Guarantor / Penjamin di Jepang
        $guarantorName = strtoupper((string) ($visaForm->nama_penanggung_jawab ?: $visaForm->nama_kaisha));
        $this->writeField($pdf, 45, 31, $guarantorName, 8.5);

        $guarantorTel = (string) ($visaForm->telepon_penanggung_jawab ?: $visaForm->telepon_jepang);
        $this->writeField($pdf, 152, 31, $guarantorTel, 8.5);

        $guarantorAddress = strtoupper(trim(($visaForm->kode_pos_jepang ? $visaForm->kode_pos_jepang.' ' : '').($visaForm->alamat_penanggung_jawab ?: $visaForm->alamat_jepang)));
        $this->writeField($pdf, 45, 41, $guarantorAddress, 8);

        // Jenis Kelamin Penanggung Jawab
        if (! empty($visaForm->jenis_kelamin_penanggung_jawab)) {
            $isGuarantorFemale = strtoupper((string) $visaForm->jenis_kelamin_penanggung_jawab) === 'P';
            if ($isGuarantorFemale) {
                $this->writeCheckbox($pdf, 155, 36.5); // Female [X]
            } else {
                $this->writeCheckbox($pdf, 140, 36.5); // Male [X]
            }
        }

        // 2. Inviter di Jepang
        $this->writeField($pdf, 45, 70, 'SAME AS ABOVE', 8.5);

        // 3. Enam Pertanyaan Kepatuhan Hukum (Have you ever: all checked NO)
        // Koordinat kolom NO [X] di halaman 2:
        $this->writeCheckbox($pdf, 188, 114); // 1. Convicted of a crime
        $this->writeCheckbox($pdf, 188, 121); // 2. Sentenced 1 year or more
        $this->writeCheckbox($pdf, 188, 132); // 3. Deported or removed
        $this->writeCheckbox($pdf, 188, 144); // 4. Drug offence
        $this->writeCheckbox($pdf, 188, 158); // 5. Prostitution
        $this->writeCheckbox($pdf, 188, 168); // 6. Human trafficking

        // 4. Tanggal Pengajuan (Date of application)
        $appDate = $visaForm->tanggal_diajukan instanceof Carbon
            ? $visaForm->tanggal_diajukan->format('d / m / Y')
            : now()->format('d / m / Y');
        $this->writeField($pdf, 50, 220, $appDate, 9);
    }

    /**
     * Helper menuliskan teks pada koordinat (X, Y) dengan font dan ukuran yang ditentukan.
     */
    protected function writeField(
        Fpdi $pdf,
        float $x,
        float $y,
        string $text,
        float $fontSize = 9,
        string $align = 'L'
    ): void {
        if ($text === '') {
            return;
        }

        $pdf->SetFont('Helvetica', '', $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $text, 0, 0, $align, false);
    }

    /**
     * Helper mencetak tanda centang [X] pada kotak checkbox.
     */
    protected function writeCheckbox(Fpdi $pdf, float $x, float $y): void
    {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY($x, $y);
        $pdf->Cell(4, 4, 'X', 0, 0, 'C', false);
    }
}

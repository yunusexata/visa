<?php

namespace App\Services\Form;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use mikehaertl\pdftk\Pdf;
use RuntimeException;

class VisaFromFillerService
{
    /** Max lengths defined inside the PDF (from fields.txt) */
    private const LIMITS = [
        'surname' => 37,
        'given_name' => 37,
        'id_no' => 18,
        'passport_no' => 9,
        'previous_stays' => 50,
        'occupation' => 68,
        'mobile' => 15,
        'stay_tel' => 15,
        'guarantor_name' => 40,
        'guarantor_tel' => 15,
        'guarantor_address' => 100,
    ];

    public function fill(Model $a, string $outPath): string
    {
        $map  = config('visa_form');
        $v    = $this->formValues($a);
        $data = [];

        foreach ($map['text'] as $key => $field) {
            $value = $v[$key] ?? '';
            if ($value === '') {
                continue;
            }
            if (isset(self::LIMITS[$key]) && mb_strlen($value) > self::LIMITS[$key]) {
                throw new InvalidArgumentException(
                    "'{$key}' is " . mb_strlen($value) . " chars, the PDF allows " . self::LIMITS[$key] . ": {$value}"
                );
            }
            $data[$field] = $value;
        }

        $data[$map['radio']['sex']]           = strtoupper($a->jenis_kelamin);                         // M / F
        $data[$map['radio']['marital']]       = $map['marital_values'][strtolower($a->status_kawin)];
        $data[$map['radio']['passport_type']] = $map['passport_type_values']['other'];
        $data[$map['radio']['guarantor_sex']] = strtoupper($a->jenis_kelamin_penanggung_jawab);

        foreach ($map['yes_no'] as $field) {
            $data[$field] = 'No';   // only safe because pernyataan_persetujuan is checked above
        }

        $template = storage_path('app/private/template_visa_tg.pdf');
        if (!is_file($template)) {
            throw new RuntimeException("Template not found: {$template}");
        }
        File::ensureDirectoryExists(dirname($outPath));

        $pdf = new Pdf($template, ['command' => env('PDFTK_BIN', 'pdftk')]);

        if ($pdf->fillForm($data)->dropXfa()->flatten()->saveAs($outPath) === false) {
            $c = $pdf->getCommand();
            throw new RuntimeException(implode("\n", [
                'pdftk failed: ' . $pdf->getError(),
                'exit code: ' . $c->getExitCode(),
                'stdout: ' . $c->getOutput(),
                'stderr: ' . $c->getError(),
                'command: ' . $c->getExecuted(),
            ]));
        }

        return $outPath;
    }

    private function formValues(Model $a): array
    {
        [$given, $surname] = $this->splitName($a->nama_lengkap);

        $date  = fn($d) => Carbon::parse($d)->format('d - m - Y');
        $phone = fn($p) => preg_replace('/[^\d+]/', '', (string) $p);

        $port = in_array(strtolower(trim($a->bandara_tujuan)), ['lainnya'], true)
            ? $a->bandara_lainnya
            : $a->bandara_tujuan;

        $previous = strtolower($a->pernah_kerja_jepang) === 'pernah'
            ? trim($a->periode_kerja_mulai . ' - ' . $a->periode_kerja_selesai)
            : 'NONE';

        // "IMIGRASI SAMBAS" is the authority; "SAMBAS" is the place of issue
        $place = trim(preg_replace(
            '/^(KANTOR\s+)?IMIGRASI\s+(KELAS\s+[IVX]+\s+)?(TPI\s+|NON\s+TPI\s+)?/i',
            '',
            $a->kantor_paspor
        ));

        $v = [
            'surname'            => $surname,
            'given_name'         => $given,
            'dob'                => $date($a->tanggal_lahir),
            'place_of_birth'     => $a->tempat_lahir . ', INDONESIA',
            'nationality'        => 'IDN',
            'id_no'              => $a->nik_ktp,
            'passport_no'        => $a->nomor_paspor,
            'passport_place'     => $place ?? $a->kantor_paspor,
            'passport_authority' => $a->kantor_paspor,
            'passport_issued'    => $date($a->tanggal_pengeluaran_paspor),
            'passport_expiry'    => $date($a->tanggal_habis_berlaku_paspor),
            'coe_no'             => $a->nomor_coe,
            'purpose'            => $a->jenis_visa->label(),
            'length_of_stay'     => $a->lama_stay_jepang,
            'arrival_date'       => Carbon::parse($a->tanggal_kedatangan)->format('d/m/Y'),
            'port_of_entry'      => $port,
            'airline'            => $a->maskapai,
            'previous_stays'     => $previous,
            'stay_name'          => $a->nama_kaisha,
            'stay_address'       => $this->join([$a->alamat_jepang, $a->kode_pos_jepang]),
            'stay_tel'           => $phone($a->telepon_jepang),
            'home_address'       => $this->join([$a->alamat_indonesia, $a->kode_pos_indonesia]),
            'mobile'             => $phone($a->telepon_indonesia),
            'email'              => $a->email_aktif,
            'occupation'         => 'UNEMPLOYED',
            'guarantor_name'     => $a->nama_penanggung_jawab,
            'guarantor_address'  => $a->alamat_penanggung_jawab,
            'guarantor_tel'      => $phone($a->telepon_penanggung_jawab),
            'inviter_name'       => 'SAME AS ABOVE',
        ];

        foreach ($v as $k => $val) {
            $val = preg_replace('/\s+/u', ' ', trim((string) $val));   // no newlines, no double spaces
            $v[$k] = $k === 'email' ? $val : mb_strtoupper($val);
        }

        return $v;
    }

    /** Last word = surname, the rest = given names. One word = surname only. */
    private function splitName(string $full): array
    {
        $parts = preg_split('/\s+/', trim($full));
        $surname = array_pop($parts);

        return [implode(' ', $parts), $surname];
    }

    private function join(array $parts): string
    {
        return implode(', ', array_filter(array_map('trim', $parts)));
    }
}

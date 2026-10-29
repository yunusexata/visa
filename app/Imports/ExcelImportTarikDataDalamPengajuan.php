<?php

namespace App\Imports;

use App\Models\GensenForm\GensenForm;
use App\Repositories\GensenForm\GensenFormRepository;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelImportTarikDataDalamPengajuan implements ToCollection, WithHeadingRow, WithChunkReading
{
    public $rows;

    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $value) {

            $this->rows = $rows
                ->map(function ($row) {

                    return [
                        'no_input_jepang' => trim($row['no_input_jepang']),
                        'nama_lengkap' => trim($row['nama_lengkap']),
                        'tahun_gensen' => trim($row['tahun_gensen']),
                    ];
                })

                ->filter(
                    fn($row) =>
                    filled($row['no_input_jepang'])
                )

                ->values()

                ->toArray();
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

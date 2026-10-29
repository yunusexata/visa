<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelImportBulk implements ToCollection, WithChunkReading, WithHeadingRow
{
    public $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $rows)
    {
        $this->rows = $this->rows->concat($rows);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

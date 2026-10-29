<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportExcel implements ToCollection
{
    private $formatCallback;

    public function __construct(callable $formatCallback = null)
    {
        $this->formatCallback = $formatCallback;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $data = call_user_func($this->formatCallback, $row);
        }
    }
}

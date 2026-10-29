<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CollectionMultiSheetExport implements WithMultipleSheets
{
    private $view;

    private $query;

    private $request;

    public function __construct($request, $query, $view)
    {
        $this->request = $request;
        $this->query = $query;
        $this->view = $view;
    }

    public function sheets(): array
    {

        $sheets = [];
        foreach ($this->query as $index => $query) {

            // dd($this->request[$index]);
            $sheets[] =
                new CollectionExport(
                    $this->request[$index],
                    $query,
                    $this->view[$index],
                );
        }

        return $sheets;
    }
}

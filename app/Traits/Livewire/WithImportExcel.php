<?php

namespace App\Traits\Livewire;

use App\Imports\ImportExcel;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

trait WithImportExcel
{
    use WithFileUploads;

    public $import_excel = [];

    public function onMount() {}

    public function mount()
    {
        $this->onMount();
    }


    public function storeImport()
    {
        foreach($this->import_excel as $import_excel)
        {
            $path = $import_excel['data']->store('temp');

            $formatFunction = $this->{$import_excel['format']}();

            $importInstance = new ImportExcel(
                $formatFunction,
            );

            Excel::import($importInstance, Storage::path($path));
            Storage::delete($path);
        }
    }

    public function render()
    {
        return view($this->getView());
    }
}

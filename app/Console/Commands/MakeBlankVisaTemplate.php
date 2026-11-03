<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use mikehaertl\pdftk\Pdf;

class MakeBlankVisaTemplate extends Command
{
    protected $signature = 'visa:blank-template';
    protected $description = 'Create a blank copy of the visa PDF template (all fields cleared)';

    // public function handle(): int
    // {

    //     $pdf = new Pdf($src, ['command' => env('PDFTK_BIN', 'pdftk')]);

    //     $fields = $pdf->getDataFields();
    //     if ($fields === false) {
    //         $this->error($pdf->getError());
    //         return self::FAILURE;
    //     }

    //     $reset = [];
    //     foreach ($fields as $f) {
    //         $name = $f['FieldName'] ?? null;
    //         $type = $f['FieldType'] ?? '';

    //         if ($name && in_array($type, ['Text', 'Choice'], true)) {
    //             $reset[$name] = '';        // clear text / dropdown
    //         } elseif ($name && $type === 'Button') {
    //             $reset[$name] = 'Off';     // untick radios / checkboxes
    //         }
    //     }

    //     // keep it fillable: no flatten here
    //     if ($pdf->fillForm($reset)->dropXfa()->saveAs($dst) === false) {
    //         $this->error($pdf->getError());
    //         return self::FAILURE;
    //     }

    //     $this->info("Created {$dst} with " . count($reset) . ' fields cleared.');
    //     return self::SUCCESS;
    // }
    public function handle(): int
    {

        $src = storage_path('app/private/template_form_visa_waiver.pdf');
        $dst = storage_path('app/private/template_visa_waiver.pdf');
        $opts = ['command' => env('PDFTK_BIN', 'pdftk')];

        // 1) read the field list (this object is now "used up")
        $reader = new Pdf($src, $opts);
        $fields = $reader->getDataFields();

        if ($fields === false) {
            $this->error($reader->getError());
            return self::FAILURE;
        }

        $reset = [];
        foreach ($fields as $f) {
            $name = $f['FieldName'] ?? null;
            $type = $f['FieldType'] ?? '';

            if ($name && in_array($type, ['Text', 'Choice'], true)) {
                $reset[$name] = '';
            } elseif ($name && $type === 'Button') {
                $reset[$name] = 'Off';
            }
        }

        // 2) fill with a NEW object
        $writer = new Pdf($src, $opts);

        if ($writer->fillForm($reset)->dropXfa()->saveAs($dst) === false) {
            $this->error($writer->getError());
            return self::FAILURE;
        }

        $this->info("Created {$dst} with " . count($reset) . ' fields cleared.');
        return self::SUCCESS;
    }
}

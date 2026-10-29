<?php

namespace App\Helpers;

use App\Exports\CollectionExport;
use App\Exports\CollectionMultiSheetExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportHelper
{
    public const TYPE_EXCEL = 'excel';

    public const TYPE_PDF = 'pdf';

    public const TYPE_PDF_DOWNLOAD = 'pdf-download';

    public const TYPE_WORD = 'word';

    public const SPN_1 = 'SPN 1';

    public const SPN_2 = 'SPN 2';

    public const SPN_3 = 'SPN 3';

    public static function export(
        $type,
        $fileName,
        $data,
        $view,
        $request,
        $paperOption = null,
        $isMultiSheet = false,
    ) {
        if ($type == self::TYPE_EXCEL) {
            return $isMultiSheet ? Excel::download(new CollectionMultiSheetExport($request, $data, $view), "$fileName.xlsx") : Excel::download(new CollectionExport($request, $data, $view), "$fileName.xlsx");
        } elseif ($type == self::TYPE_WORD) {
            // $publicPath = $view;
            // $template = new TemplateProcessor($publicPath);

            // if (isset($data['clone_block'])) {
            //     foreach ($data['clone_block'] as $block_name => $clone_block) {
            //         $template->cloneBlock($block_name, $clone_block, true, true);
            //     }
            // }

            // if (isset($data['block_replacement'])) {
            //     foreach ($data['block_replacement'] as $block_name => $block_replacement) {
            //         $template->cloneBlock($block_name, 0, true, false, $block_replacement);
            //     }
            // }

            // if (isset($data['table_replacement'])) {
            //     foreach ($data['table_replacement'] as $key => $value) {
            //         $template->cloneRowAndSetValues($key, $value);
            //     }
            // }

            // if (isset($data['table'])) {
            //     $table = new Table();
            //     foreach ($data['table'] as $key => $row) {
            //         foreach ($row as $cols) {
            //             $table->addRow();
            //             foreach ($cols as $col) {
            //                 $table->addCell($col['width'], isset($col['option']) ? $col['option'] : null)->addText($col['value'], isset($col['text_style']) ? $col['text_style'] : null, isset($col['text_option']) ? $col['text_option'] : null);

            //             }
            //         }
            //     }

            //     $template->setComplexBlock('table', $table);
            // }

            // $template->setValues($data['normal_replacement']);
            // $tempPath = storage_path('app/temp');
            // if (!file_exists($tempPath)) {
            //     mkdir($tempPath, 0777, true);
            // }
            // $filePath = $tempPath . '/' . $fileName . '.docx';
            // $template->saveAs($filePath);

            // return response()->download($filePath)->deleteFileAfterSend(false);
        } else {
            $pdf = Pdf::loadview(
                $view,
                [
                    'request' => $request,
                    'collection' => $data,
                    'number_format' => true,
                ],
            );

            if ($paperOption) {
                $pdf = $pdf->setPaper($paperOption['size'], $paperOption['orientation']);
            }

            return response()->stream(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$fileName.'.pdf"',
                ]
            );
        }
    }
}

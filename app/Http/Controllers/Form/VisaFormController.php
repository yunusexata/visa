<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Form\VisaForm;
use App\Repositories\Form\VisaFormRepository;
use App\Services\Form\VisaFromFillerService;
use App\Services\Form\VisaJapanPdfService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisaFormController extends Controller
{
    public function index()
    {
        return view('app.form.visa-form.index');
    }

    public function attachments(int $id)
    {
        $visaForm = VisaForm::findOrFail($id);

        return view('app.form.visa-form.attachments', [
            'visaFormId' => $id,
            'visaForm' => $visaForm,
        ]);
    }

    public function generatePdf(int $id)
    {
        $visaForm = VisaForm::findOrFail($id);
        $filename = "FORM PENGAJUAN VISA TG - AN {$visaForm->nama_lengkap} " . now()->year . ".pdf";

        $filler = new VisaFromFillerService();
        $out = storage_path("app/public/{$filename}.pdf");
        $filler->fill($visaForm, $out);

        return response()->streamDownload(function () use ($out) {
            readfile($out);
            @unlink($out);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }
}

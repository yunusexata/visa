<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Form\VisaForm;
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

    public function generatePdf(int $id, VisaJapanPdfService $service): StreamedResponse
    {
        $visaForm = VisaForm::findOrFail($id);
        $pdfBinary = $service->generate($visaForm);
        $safeNoReg = str_replace(['/', '\\'], '_', $visaForm->nomor_registrasi ?: (string) $visaForm->id);
        $filename = "Visa_Japan_Application_{$safeNoReg}.pdf";

        return response()->streamDownload(function () use ($pdfBinary) {
            echo $pdfBinary;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}

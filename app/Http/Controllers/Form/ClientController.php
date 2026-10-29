<?php

namespace App\Http\Controllers\MasterClient;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\Client\ClientRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use setasign\Fpdi\Fpdi;

class ClientController extends Controller
{
    public function form_confirmation()
    {

        return view('app.client.form-confirmation');
    }
    public function document()
    {
        $user = auth()->user();

        if ($user && $user->hasRole(\App\Models\User::ROLE_CLIENT)) {
            $clientId = $user->client_id;

            // Retrieve documents for this client keyed by their type
            $documents = \App\Models\Document::where('client_id', $clientId)
                ->get()
                ->keyBy('type');

            return view('app.client.document', compact('documents'));
        }
        return view('app.client.document');
    }
    public function agreement()
    {
        $user = auth()->user();

        if ($user && $user->hasRole(\App\Models\User::ROLE_CLIENT)) {
            $clientId = $user->client_id;

            // Retrieve documents for this client keyed by their type
            $documents = \App\Models\Document::where('client_id', $clientId)
                ->get()
                ->keyBy('type');

            return view('app.client.agreement', compact('documents'));
        }
        return view('app.client.agreement');
    }

    public function create(Request $request)
    {
        return view('app.client.create', ["token" => $request->token]);
    }



    // $this->drawLabelPdf(, 'result', $user);
    public function download(Request $request)
    {

        $model = auth()->user();
        if (!$model->hasRole(User::ROLE_CLIENT)) {
            $client_id = Crypt::decrypt($request->client_id);
            $model = UserRepository::findBy([
                ['client_id', $client_id]
            ]);
            $client = ClientRepository::find($client_id);
            $esign = $client->esign;
        } else {
            if (!$model || !$model->client || !$model->client->esign) {
                abort(404, 'Dokumen Perjanjian belum ditandatangani.');
            }
            $esign = $model->client->esign;
        }




        // Try to serve pre-generated file from storage first
        if ($esign->path && \Illuminate\Support\Facades\Storage::disk('local')->exists($esign->path)) {
            $content = \Illuminate\Support\Facades\Storage::disk('local')->get($esign->path);
            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header(
                    'Content-Disposition',
                    'attachment; filename="Surat Perjanjian.pdf"'
                );
        }

        // Fallback: Generate dynamically on-the-fly and cache/store it
        $pdfBinary = \App\Models\Client\ClientEsign::generatePdfForUser($model);

        try {
            $pdfPath = 'esigns/' . $model->client->id . '_agreement.pdf';
            \Illuminate\Support\Facades\Storage::disk('local')->put($pdfPath, $pdfBinary);
            $esign->update([
                'disk' => 'local',
                'path' => $pdfPath,
                'stored_name' => basename($pdfPath),
                'extension' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => \Illuminate\Support\Facades\Storage::disk('local')->size($pdfPath),
            ]);
        } catch (\Throwable $e) {
            logger()->error("Failed to store dynamically generated PDF on-the-fly", ['exception' => $e->getMessage()]);
        }

        return response($pdfBinary)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'attachment; filename="Surat Perjanjian.pdf"'
            );
    }
}

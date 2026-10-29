<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client\ClientEsign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EsignController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        // dd($user);
        $client = $user->client;

        if (!$client) {
            return redirect()->route('dashboard.index')->with('error', 'Data klien tidak ditemukan.');
        }

        // Klien harus sudah mengisi konfirmasi terlebih dahulu
        if (!$client->confirmation) {
            return redirect()->route('dashboard.index')->with('error', 'Silakan isi formulir konfirmasi terlebih dahulu.');
        }

        // Hanya tipe klaim SPEED atau ADJUSTMENT yang wajib E-Sign
        $claimType = strtoupper($client->confirmation->claim_type ?? '');
        if (!in_array($claimType, ['SPEED', 'ADJUSTMENT', 'ADJUSMENT'])) {
            return redirect()->route('dashboard.index');
        }

        // Check if already signed
        if ($client->esign()->exists()) {
            return redirect()->route('dashboard.index')->with('info', 'Anda sudah menandatangani perjanjian.');
        }

        $now = Carbon::now()->locale('id');
        $dateFormatted = $now->translatedFormat('l, d F Y');
        $letterNumber = str_pad($client->id, 4, '0', STR_PAD_LEFT) . '/LSN/SREI/2026';

        // Ambil NIK dan Alamat dari tabel client_confirmations
        $nik = $client->confirmation->nik ?? '';
        $address = $client->confirmation->indonesia_address ?? '';
        // dd(auth()->user()->hasRole(\App\Models\User::ROLE_CLIENT));
        // dd([
        //     $client,
        //     $dateFormatted,
        //     $letterNumber,
        //     $nik,
        //     $address
        // ]);

        return view('client.esign', compact('client', 'dateFormatted', 'letterNumber', 'nik', 'address'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            return redirect()->route('dashboard.index')->with('error', 'Data klien tidak ditemukan.');
        }

        // Validasi tipe klaim
        $claimType = strtoupper($client->confirmation->claim_type ?? '');
        if (!in_array($claimType, ['SPEED', 'ADJUSTMENT', 'ADJUSMENT'])) {
            return redirect()->route('dashboard.index');
        }

        if ($client->esign()->exists()) {
            return redirect()->route('dashboard.index')->with('info', 'Anda sudah menandatangani perjanjian.');
        }

        $request->validate([
            'nik' => 'required|string|max:20',
            'address' => 'required|string',
            'agreement_accepted' => 'required|accepted',
            'signature_data' => 'required|string', // base64 string
        ]);

        // Generate Document Hash
        $now = Carbon::now()->locale('id');
        $dateFormatted = $now->translatedFormat('l, d F Y');
        $letterNumber = str_pad($client->id, 4, '0', STR_PAD_LEFT) . '/LSN/SREI/2026';

        // Render the agreement content to string for hashing
        $agreementHtml = view('client.partials.agreement-content', [
            'client' => $client,
            'nik' => $request->nik,
            'address' => $request->address,
            'dateFormatted' => $dateFormatted,
            'letterNumber' => $letterNumber,
        ])->render();

        $documentHash = hash('sha256', $agreementHtml);

        // Save to Database with signed_at initially null to prevent observer race condition
        $esign = ClientEsign::create([
            'client_id' => $client->id,
            'nik' => $request->nik,
            'address' => $request->address,
            'agreement_version' => 'v1.0',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'document_hash' => $documentHash,
            'signature_data' => $request->signature_data,
            'signed_at' => null,
        ]);

        $pdfStored = false;
        $pdfPath = 'esigns/' . $client->id . '_agreement.pdf';

        try {
            // Generate and save the PDF file
            $pdfContent = ClientEsign::generatePdfForUser($user);
            \Illuminate\Support\Facades\Storage::disk('local')->put($pdfPath, $pdfContent);
            $pdfStored = true;
        } catch (\Throwable $e) {
            logger()->error("Failed to generate and store agreement PDF during sign store", [
                'exception' => $e->getMessage(),
                'client_id' => $client->id,
            ]);
        }

        if ($pdfStored) {
            $esign->update([
                'disk' => 'local',
                'path' => $pdfPath,
                'stored_name' => basename($pdfPath),
                'extension' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => \Illuminate\Support\Facades\Storage::disk('local')->size($pdfPath),
                'signed_at' => Carbon::now('Asia/Jakarta'),
            ]);
        } else {
            $esign->update([
                'signed_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }

        return redirect()->route('dashboard.index');
    }
}

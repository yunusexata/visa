<?php

namespace App\Models\Client;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;
use setasign\Fpdi\Fpdi;

class ClientEsign extends Model
{
    use HasFactory, HasTrackHistory, SoftDeletes;

    protected $table = 'client_esigns';

    protected $fillable = [
        'client_id',
        'nik',
        'address',
        'agreement_version',
        'ip_address',
        'user_agent',
        'document_hash',
        'signature_data',
        'signed_at',
        'stored_name',
        'disk',
        'path',
        'note',
        'extension',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected static function onBoot()
    {
        self::created(function ($model) {
            $model->client->update([
                'claim_type' => $model->client->confirmation->claim_type,
                'is_exported' => false,
                'exported_at' => null,
            ]);
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public static function generatePdfForUser(User $user): string
    {
        $imagePath = public_path('assets/pdf/perjanjian_nenkin_20.pdf');
        $pdf = new Fpdi;

        $pageWidth = 1130;
        $pageHeight = 1680;

        $pdf->AddPage('P', [$pageWidth, $pageHeight]);
        $pdf->setSourceFile($imagePath);
        $template = $pdf->importPage(1);

        $pdf->useTemplate(
            $template,
            0,
            0,
            $pageWidth,
            $pageHeight
        );

        $pdf->SetFont('Times', '', 50);

        // NO SURAT
        $pdf->Text(508, 300, str_pad($user->client_id, 4, '0', STR_PAD_LEFT));
        // HARI
        $pdf->Text(450, 356, Carbon::parse($user->client->esign->signed_at)->locale('id')
            ->translatedFormat('l'));
        $pdf->Text(600, 356, Carbon::parse($user->client->esign->signed_at)->locale('id')
            ->translatedFormat('d F Y'));
        // NO Nama
        $pdf->Text(585, 440, $user->name);
        // NIK
        $pdf->Text(585, 462, $user->client->esign->nik);
        // Tgl Lahir
        $pdf->Text(585, 484, Carbon::parse($user->client->dob)->locale('id')
            ->translatedFormat('d F Y'));
        // Alamat
        $clientModel = $user->client;
        $address = $clientModel->esign?->address
            ?? $clientModel->confirmation?->indonesia_address
            ?? '-';
        // $line1 = (string) substr($address, 0, 40);
        // $line2 = strlen($address) > 40 ? (string) substr($address, 40, 50) : '';
        // $line3 = strlen($address) > 90 ? (string) substr($address, 90, 50) : '';

        // $lines = [
        //     $line1,
        //     $line2,
        //     $line3,
        // ];
        $line1 = substr($address, 0, 38);
        $line2 = substr($address, 38, 50);
        $line3 = substr($address, 88, 50);

        $lines = array_filter([
            $line1,
            $line2,
            $line3,
        ]);
        $y = 506;

        foreach ($lines as $index => $line) {
            if ($index < 3) {
                if ($index) {
                    $x = 500;
                } else {
                    $x = 585;
                }
                $pdf->Text($x, $y, $line);
                $y += 22; // line spacing
            }
        }

        $signature = $user->client->esign->signature_data;

        // Remove data URI prefix
        $signature = preg_replace(
            '/^data:image\/\w+;base64,/',
            '',
            $signature
        );

        // Create temporary file
        $tempFile = storage_path('app/temp-signature-'.uniqid().'.png');

        $tempDir = storage_path('app');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        file_put_contents(
            $tempFile,
            base64_decode($signature)
        );

        // Insert image into PDF
        $pdf->Image(
            $tempFile,
            670, // X
            1450, // Y
            200, // Width
            80   // Height
        );

        // Delete temp file
        unlink($tempFile);

        // NO Nama
        $pdf->SetFont('Times', '', 50);

        $leftX = 610;
        $rightX = 940;

        $text = strtoupper($user->name);
        $lines = str_split($text, 38);

        $y = 1560;
        foreach ($lines as $index => $line) {
            if ($index < 3) {
                $textWidth = $pdf->GetStringWidth($line);
                $x = $leftX + (($rightX - $leftX) - $textWidth) / 2;
                $pdf->Text($x, $y, $line);
                $y += 22; // line spacing
            }
        }

        return $pdf->Output('S');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updator()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function deletor()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }
}

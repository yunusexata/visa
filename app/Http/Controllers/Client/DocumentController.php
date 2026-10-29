<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Download document via signed URL with 15 minutes expiration.
     */
    public function download($type, Request $request)
    {
        // Ensure the logged in user is a client and has an associated client ID

        $model = auth()->user();
        if ($model->hasRole(User::ROLE_SUPER_ADMIN)) {
            $clientId = Crypt::decrypt($request->client_id);
        } else {
            $clientId = $model->client_id;
        }

        if (!$clientId) {
            abort(403, 'Akses ditolak: User tidak terasosiasi dengan klien manapun.');
        }

        // Find the document of specific type for this client
        $document = Document::where('client_id', $clientId)
            ->where('type', $type)
            ->firstOrFail();
        $content = Storage::disk('supabase')->get($document->bucket_path);

        // Record live download activity for PT Sumber Rezeki Exata Indonesia payroll audit
        if ($model->hasRole(User::ROLE_CLIENT)) {
            try {
                DB::table('document_downloads')->insert([
                    'client_id'     => $clientId,
                    'document_id'   => $document->id,
                    'batch_id'      => $document->batch_id,
                    'ip_address'    => request()->ip(),
                    'user_agent'    => request()->userAgent(),
                    'downloaded_at' => now(),
                    'source'        => 'live_tracked',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } catch (\Throwable $e) {
                logger()->warning('Failed to record document download activity: ' . $e->getMessage(), [
                    'client_id'   => $clientId,
                    'document_id' => $document->id,
                ]);
            }
        }

        return response($content)
            ->header('Content-Type', Storage::disk('supabase')->mimeType($document->bucket_path))
            ->header(
                'Content-Disposition',
                'attachment; filename="' . basename($document->bucket_path) . '"'
            );
        // return redirect($url);
    }
}

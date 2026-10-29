<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EsignRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->hasRole(User::ROLE_CLIENT)) {
            $client = $user->client;

            // Jika client sudah mengisi form konfirmasi
            if ($client && $client->confirmation) {
                // Ambil claim_type (antisipasi huruf besar/kecil dan typo Yunus 'adjusment')
                $claimType = strtoupper($client->claim_type ?? '');

                // Cek apakah masuk kategori yang wajib E-Sign (SPEED atau ADJUSTMENT)
                if (in_array($claimType, ['SPEED', 'ADJUSTMENT', 'ADJUSMENT'])) {
                    // Jika belum tanda tangan, paksa ke halaman e-sign
                    if (! $client->esign()->exists()) {
                        // Hindari loop jika memang sedang di halaman esign
                        if (! $request->is('esign*')) {
                            return redirect()->route('client.esign.show');
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}

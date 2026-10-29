<?php

namespace App\Http\Controllers;

use App\Repositories\Client\ClientRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('app.dashboard.index');
    }

    public function povClient(Request $request)
    {
        $client_id = Crypt::decrypt($request->id);
        $client = ClientRepository::find($client_id);

        // Eager-load all relations in one go
        $client->loadMissing(['confirmation', 'esign', 'review', 'documents']);

        $documents = $client->documents->keyBy('type');

        // Build progress steps for the tracker
        $steps = [
            [
                'key' => 'confirmation',
                'label' => 'Konfirmasi',
                'done' => (bool) $client->confirmation,
                'icon' => 'check_circle',
            ],
            [
                'key' => 'claim_process',
                'label' => 'Proses Klaim',
                'done' => $client?->confirmation?->claim_type === 'normal'
                    || (bool) ($client?->esign),
                'icon' => 'folder',
            ],
            [
                'key' => 'review',
                'label' => 'Uang Ditransfer',
                'done' => $documents->has('ichijikin') && $documents->has('resi') && $documents->has('kwitansi'),
                'icon' => 'star',
            ],
        ];

        $completedSteps = collect($steps)->where('done', true)->count();
        $totalSteps = count($steps);
        $client_id = $request->id;

        return view('app.dashboard.client-home', compact(
            'client',
            'documents',
            'steps',
            'completedSteps',
            'totalSteps',
            'client_id'
        ));
    }
}

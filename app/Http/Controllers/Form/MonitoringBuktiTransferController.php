<?php

namespace App\Http\Controllers\MasterClient;

use App\Http\Controllers\Controller;

class MonitoringBuktiTransferController extends Controller
{
    public function index()
    {
        return view('app.monitoring-bukti-transfer.index');
    }
}

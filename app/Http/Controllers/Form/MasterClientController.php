<?php

namespace App\Http\Controllers\MasterClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterClientController extends Controller
{
    public function index()
    {
        return view('app.master-client.index');
    }

    public function create()
    {
        return view('app.master-client.import', ['objId' => null]);
    }

    public function edit(Request $request)
    {
        return view('app.master-client.detail', ['objId' => $request->id]);
    }
}

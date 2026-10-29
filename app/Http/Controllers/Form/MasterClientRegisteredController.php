<?php

namespace App\Http\Controllers\MasterClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterClientRegisteredController extends Controller
{
    public function index()
    {
        return view('app.master-client-registered.index');
    }

    public function create()
    {
        return view('app.master-client-registered.import', ['objId' => null]);
    }

    public function edit(Request $request)
    {
        return view('app.master-client-registered.detail', ['objId' => $request->id]);
    }
}

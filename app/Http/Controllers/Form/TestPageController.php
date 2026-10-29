<?php

namespace App\Http\Controllers\MasterClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestPageController extends Controller
{
    public function index()
    {
        return view('app.test-page.index');
    }

    public function create()
    {
        return view('app.test-page.import', ['objId' => null]);
    }

    public function edit(Request $request)
    {
        return view('app.test-page.detail', ['objId' => $request->id]);
    }
}

<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VisaFormLinkController extends Controller
{
    public function index()
    {
        return view('app.form.visa-form-link.index');
    }

    public function create()
    {
        return view('app.form.visa-form-link.detail', ['objId' => null]);
    }

    public function edit(Request $request)
    {
        return view('app.form.visa-form-link.detail', ['objId' => $request->id]);
    }
}

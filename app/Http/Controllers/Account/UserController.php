<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('app.account.user.index');
    }

    public function create()
    {
        return view('app.account.user.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.account.user.detail', ["objId" => $request->id]);
    }
}

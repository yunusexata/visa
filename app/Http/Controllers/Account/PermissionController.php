<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return view('app.account.permission.index');
    }

    public function create()
    {
        return view('app.account.permission.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.account.permission.detail', ["objId" => $request->id]);
    }
}

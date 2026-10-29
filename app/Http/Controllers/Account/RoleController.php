<?php

namespace App\Http\Controllers\Account;

use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('app.account.role.index');
    }

    public function create()
    {
        return view('app.account.role.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.account.role.detail', ["objId" => $request->id]);
    }
}

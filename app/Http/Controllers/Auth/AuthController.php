<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Account\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('app.auth.login');
    }

    public function login()
    {
        return view('app.auth.admin-login');
    }

    public function adminLogin()
    {
        return view('app.auth.admin-login');
    }

    public function register()
    {
        return view('app.auth.register');
    }

    public function forgotPassword()
    {
        return view('app.auth.forgot-password');
    }

    public function resetPassword(Request $request)
    {
        return view('app.auth.reset-password', ['token' => $request->token, 'email' => $request->email]);
    }

    public function emailVerification(Request $request)
    {
        return view('app.auth.email-verification', ['email' => $request->email]);
    }

    public function profile()
    {
        return view('app.auth.profile');
    }

    public function emailVerificationVerify(Request $request)
    {
        $user = UserRepository::find($request->id);
        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->hash)) {
            return redirect()->route('login');
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        Auth::loginUsingId($user->id);

        return redirect()->route('dashboard.index');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }
}

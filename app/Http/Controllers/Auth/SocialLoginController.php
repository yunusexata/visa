<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client\ClientMagicLink;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\Client\ClientMagicLinkRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    // Redirect the user to the Google authentication page
    public function redirect($provider)
    {
        if (!in_array($provider, ['google'])) {
            return redirect('/login')->with('error', 'Login tidak berhasil.');
        }
        return Socialite::driver($provider)->redirect();
    }

    // Handle the callback from Google
    public function callback($provider)
    {
        if (!in_array($provider, ['google'])) {
            return redirect('/login')->with('error', 'Login tidak berhasil.');
        }
        try {
            $socialUser = Socialite::driver($provider)->user();
            // 1. Check for the new array session key
            if (session()->has('pending_magic_link_data')) {
                return $this->processMagicLinkRegistration($socialUser, $provider);
            }
            // Check if user already exists
            $user = User::where('email', $socialUser->getEmail())
                ->where('provider_name', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();
            if (!$user) {
                return redirect('/login')->with('error', 'Akun tidak ditemukan. Silakan register terlebih dahulu.');
            }

            Auth::login($user, true);
            session()->regenerate();
            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            // Log the error or handle it gracefully
            return redirect('/login')->with('error', 'Authentication failed, please try again.');
        }
    }
    private function processMagicLinkRegistration($socialUser, $provider)
    {
        // Extract the array from the session
        $sessionData = session('pending_magic_link_data');
        $token = $sessionData['token'] ?? null;
        $name = $sessionData['name'] ?? null;
        $dob = $sessionData['dob'] ?? null;

        $email = $socialUser->getEmail();

        // Clear session aggressively if data is missing
        if (!$token || !$name || !$dob) {
            session()->forget('pending_magic_link_data');
            return redirect('/login')->with('error', 'Sesi register tidak valid atau kadaluarsa.');
        }
        $user = UserRepository::findByEmail($email);

        if (!empty($user)) {
            session()->forget('pending_magic_link_data');
            return redirect('/login')->with('error', 'Register Gagal: Email Sudah Digunakan');
        }
        try {
            $magicLink = ClientMagicLinkRepository::findBy([
                ['token', $token]
            ]);

            if (!$magicLink || !$magicLink->client) {
                session()->forget('pending_magic_link_data');
                return redirect('/login')->with('error', 'Token tidak valid.');
            }
            $client = $magicLink->client;

            // ==========================================
            // RE-VALIDATION CHECK
            // ==========================================
            $nameMatched = mb_strtolower(trim($client->name)) === mb_strtolower(trim($name));
            $dobMatched = Carbon::parse($client->dob)->isSameDay(Carbon::parse($dob));
            if (! $nameMatched || ! $dobMatched) {
                session()->forget('pending_magic_link_data');
                return redirect('/login')->with('error', 'Akun Tidak Sesuai, hubungi PIC.');
            }
            // ==========================================

            // Validation passed! Proceed with DB Transaction
            DB::beginTransaction();

            $randomPassword = Str::random(16);

            $magicLink->update([
                'used_at' => now(),
                'email' => $email,
                'password' => $randomPassword,
            ]);

            $user = UserRepository::create([
                'client_id' => $magicLink->client_id,
                'name' => $client->name,
                'no_whatsapp' => $client->phone,
                'email' => $email,
                'password' => Hash::make($randomPassword),
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
                'pic' => $client->pic,
                'subject_id' => $magicLink->id,
                'subject_type' => ClientMagicLink::class,
            ]);

            $user->assignRole(User::ROLE_CLIENT);

            Auth::login($user);
            DB::commit();

            session()->forget('pending_magic_link_data');

            return redirect('/dashboard')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->forget('pending_magic_link_data');
            return redirect('/login')->with('error', "Gagal: " . $e->getMessage());
        }
    }
}

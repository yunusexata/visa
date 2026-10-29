<?php

namespace App\Livewire\Auth;

use App\Helpers\Alert;
use App\Repositories\Account\UserRepository;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;

class Login extends Component
{
    #[Validate('required', message: 'Nama / Email Harus Diisi', onUpdate: false)]
    public $nameOrEmail;

    #[Validate('required', message: 'Tanggal Harus Diisi', onUpdate: false)]
    public $tanggal;

    #[Validate('required', message: 'Bulan Harus Diisi', onUpdate: false)]
    public $bulan;

    #[Validate('required', message: 'Tahun Harus Diisi', onUpdate: false)]
    public $tahun;


    public function registerWithGoogle()
    {
        return redirect()->route('auth.socialite.redirect', 'google');
    }

    public function store()
    {
        $this->validate();

        $user = UserRepository::findByNameOrEmail($this->nameOrEmail);
        if (empty($user)) {
            Alert::fail($this, 'Login Gagal', 'Akun Belum Terdaftar');
            return;
        }

        if (!$user->hasRole(\App\Models\User::ROLE_CLIENT)) {
            Alert::fail($this, 'Akses Ditolak', 'Akun Belum Terdaftar');
            return;
        }

        $formattedDob = sprintf('%04d-%02d-%02d', $this->tahun, $this->bulan, $this->tanggal);

        $clientDob = null;
        if ($user->client && $user->client->dob) {
            $clientDob = \Carbon\Carbon::parse($user->client->dob)->format('Y-m-d');
        }

        if (!$clientDob || $clientDob !== $formattedDob) {
            Alert::fail($this, 'Login Gagal', 'Tanggal Lahir Tidak Sesuai');
            return;
        }

        Auth::loginUsingId($user->id);
        $this->redirectRoute('dashboard.index');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

<?php

namespace App\Livewire\Auth;

use App\Enums\Auth\Role;
use App\Helpers\Alert;
use App\Repositories\Account\UserRepository;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;

class AdminLogin extends Component
{
    #[Validate('required', message: 'Nama / Email Harus Diisi', onUpdate: false)]
    public $nameOrEmail;

    #[Validate('required', message: 'Password Harus Diisi', onUpdate: false)]
    public $password;

    public function store()
    {
        $this->validate();

        $user = UserRepository::findByNameOrEmail($this->nameOrEmail);
        if (empty($user)) {
            Alert::fail($this, 'Login Gagal', 'Akun Belum Terdaftar');
            return;
        }

        if (!$user->hasAnyRole([User::ROLE_SUPERADMIN, User::ROLE_ADMIN])) {
            Alert::fail(
                $this,
                'Akses Ditolak',
                'Maaf, halaman ini hanya dapat diakses oleh Administrator.'
            );
            return;
        }

        if (!Hash::check($this->password, $user->password)) {
            Alert::fail($this, 'Login Gagal', 'Password Tidak Sesuai');
            return;
        }

        Auth::loginUsingId($user->id);
        $this->redirectRoute('dashboard.index');
    }

    public function render()
    {
        return view('livewire.auth.admin-login');
    }
}

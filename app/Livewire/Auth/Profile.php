<?php

namespace App\Livewire\Auth;

use Exception;
use App\Helpers\Alert;
use App\Repositories\Account\UserRepository;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;

class Profile extends Component
{
    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $name;

    public $email;
    public $role;
    public $pic_code;
    public $no_whatsapp;
    public $password_gensen_form;
    public $pic_code_choice = [];

    public $oldPassword;
    public $password;
    public $retypePassword;

    public function mount()
    {
        $user = UserRepository::authenticatedUser();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->no_whatsapp = $user->no_whatsapp;
        $this->password_gensen_form = $user->password_gensen_form;
        $this->pic_code = $user->pic_code;
        $this->role = $user->roles[0]->name;

        $this->pic_code_choice = UserRepository::getPicCodeChoice();
    }

    public function store()
    {
        $this->validate();

        $user = UserRepository::authenticatedUser();

        $validatedData = [
            'name' => $this->name,
            'pic_code' => $this->pic_code,
            'no_whatsapp' => $this->no_whatsapp,
            'password_gensen_form' => $this->password_gensen_form,
        ];
        if (!empty($this->oldPassword) || !empty($this->password) || !empty($this->retypePassword)) {
            if (empty($this->oldPassword)) {
                Alert::fail($this, "Gagal", "Password Lama Harus Diisi");
                return;
            }
            if (empty($this->password)) {
                Alert::fail($this, "Gagal", "Password Baru Harus Diisi");
                return;
            }
            if (empty($this->retypePassword)) {
                Alert::fail($this, "Gagal", "Ketik Ulang Password Baru Harus Diisi");
                return;
            }
            if ($this->retypePassword != $this->password) {
                Alert::fail($this, "Gagal", "Pengetikan Ulang Password Baru Tidak Sama");
                return;
            }
            if (!Hash::check($this->oldPassword, $user->password)) {
                Alert::fail($this, 'Gagal', 'Password Lama Tidak Sesuai');
                return;
            }

            $validatedData['password'] = Hash::make($this->password);
        }

        try {
            DB::beginTransaction();
            UserRepository::update($user->id, $validatedData);
            Alert::success($this, 'Berhasil', 'Profil berhasil diperbarui');
            DB::commit();

            $this->redirectRoute('profile');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.profile');
    }
}

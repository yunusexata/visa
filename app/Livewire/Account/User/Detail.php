<?php

namespace App\Livewire\Account\User;

use App\Helpers\Alert;
use App\Models\User;
use App\Repositories\Account\RoleRepository;
use App\Repositories\Account\UserRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Detail extends Component
{
    public $objId;

    public $roles = [];

    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $name;

    #[Validate('required', message: 'Email Harus Diisi', onUpdate: false)]
    #[Validate('email', message: 'Format Email Tidak Sesuai', onUpdate: false)]
    public $email;

    #[Validate('required', message: 'Jabatan Harus Dipilih', onUpdate: false)]
    public $role;

    public $password;

    public $upod_type;

    public function mount()
    {
        $this->roles = RoleRepository::getIdAndNames()->pluck('name');
        $this->role = $this->roles[0];
        $this->upod_type = User::UPOD_TYPE_GOOGLE;

        if ($this->objId) {
            $user = UserRepository::find($this->objId);

            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->roles[0]->name;
            $this->upod_type = $user->upod_type;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            return;
        }

        $this->name = '';
        $this->email = '';
        $this->upod_type = User::UPOD_TYPE_GOOGLE;
        $this->role = $this->roles[0];
        $this->password = '';
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('user.index');
    }

    public function store()
    {
        $this->validate();

        $otherUser = UserRepository::findByEmail($this->email);
        if (! empty($otherUser) && $otherUser->id != $this->objId) {
            Alert::fail($this, 'Gagal', 'Email telah digunakan pada akun yang lainnya. Silahkan gunakan email lain.');

            return;
        }

        if (empty($this->objId) && empty($this->password)) {
            Alert::fail($this, 'Gagal', 'Password Harus Diisi');

            return;
        }

        $validatedData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->role === User::ROLE_SALES) {
            $validatedData['upod_type'] = $this->upod_type;
        }

        if (! empty($this->password)) {
            $validatedData['password'] = Hash::make($this->password);
        }

        try {
            DB::beginTransaction();
            if ($this->objId) {
                UserRepository::update($this->objId, $validatedData);
                $user = UserRepository::find($this->objId);
                $user->syncRoles($this->role);
            } else {
                $user = UserRepository::create($validatedData);
                $user->assignRole($this->role);
            }
            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                'Berhasil',
                'Pengguna Berhasil Diperbarui',
                'on-dialog-confirm',
                'on-dialog-cancel',
                'Oke',
                'Tutup',
            );
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, 'Gagal', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.account.user.detail');
    }
}

<?php

namespace App\Livewire\GensenForm\GensenFormLink;

use App\Helpers\Alert;
use App\Helpers\AppCrypt;
use App\Models\GensenForm\GensenFormLink;
use App\Repositories\GensenForm\GensenFormLinkRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Detail extends Component
{
    use WithFileUploads;

    public $objId;

    public $password;

    public $expired_at;

    public $max_usage = 1;

    public $name;

    public $link;

    public $user;

    public function mount()
    {
        $this->expired_at = Carbon::now()->addDays(7)->format('Y-m-d\TH:i');
        $this->password = Auth::user()->password_gensen_form;
        if ($this->objId) {
            $id = AppCrypt::decrypt($this->objId);
            if (! $id) {
                abort(404, 'Link tidak valid atau telah dimanipulasi.');
            }
            $form = GensenFormLinkRepository::find($id);
            if (! $form) {
                abort(404, 'Form Tidak Ditemukan');
            }
            $this->password = $form->password;
            $this->max_usage = $form->max_usage;
            $this->name = $form->name;
            $this->expired_at = Carbon::parse($form->expired_at)->format('Y-m-d\TH:i');
            $this->link = route('gensen_form.form', $form->token);
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('gensen_form_link.index');
        } else {
            $this->redirectRoute('gensen_form_link.index');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('gensen_form_link.index');
    }

    public function store()
    {
        try {
            DB::transaction(function () {
                // Vehicle
                $validateData = [
                    'expired_at' => $this->expired_at,
                    'password' => $this->password,
                    'max_usage' => $this->max_usage,
                    'name' => $this->name,
                    'status' => GensenFormLink::STATUS_ACTIVE,
                ];
                $vehicle_id = null;
                if ($this->objId) {
                    $regency_id = AppCrypt::decrypt($this->objId);
                    GensenFormLinkRepository::update($regency_id, $validateData);
                } else {
                    $vehicle = GensenFormLinkRepository::create($validateData);
                    $vehicle_id = $vehicle->id;
                }
            });

            DB::commit();
            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                'Berhasil',
                'Data Berhasil Diperbarui',
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
        return view('livewire.gensen-form.gensen-form-link.detail');
    }
}

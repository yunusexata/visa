<?php

namespace App\Livewire\Client;

use App\Helpers\Alert;
use App\Helpers\NumberFormatter;
use App\Models\Client\Client;
use App\Models\Client\ClientConfirmation;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\Client\ClientConfirmationRepository;
use App\Repositories\Client\ClientMagicLinkRepository;
use App\Repositories\Client\ClientRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class FormConfirmation extends Component
{
    public $name;
    public $claim_type;

    #[Validate('required|in:62,81', message: [
        'required' => 'Kode negara nomor WhatsApp wajib dipilih.',
        'in' => 'Kode negara harus IND (+62) atau JPN (+81).',
    ], onUpdate: false)]
    public $phone_country_code = '';

    #[Validate('required|numeric|regex:/^[1-9][0-9]*$/', message: [
        'required' => 'Nomor WhatsApp saat ini wajib diisi.',
        'numeric' => 'Nomor WhatsApp hanya boleh berisi angka.',
        'regex' => 'Nomor WhatsApp tidak boleh diawali dengan angka 0.',
    ], onUpdate: false)]
    public $phone_number = '';

    #[Validate('required', message: 'Status Penerimaan Nenkin 80% Harus Diisi', onUpdate: false)]
    public $nenkin_80_received_type;
    #[Validate('required', message: 'Posisi Saat Ini Harus Diisi', onUpdate: false)]
    public $current_position;
    public $back_to_jp_type;
    public $departure_type;
    public $departure;
    public $departure_month;
    public $departure_year;
    public $estimated_return;
    public $estimated_return_month;
    public $estimated_return_year;
    #[Validate('required', message: 'Alamat Indonesia Harus Diisi', onUpdate: false)]
    public $indonesia_address;
    #[Validate(
        'required|numeric|digits:16',
        message: [
            'required' => 'NIK Harus Diisi',
            'numeric' => 'NIK Hanya Boleh Angka',
            'digits' => 'NIK Harus 16 Digit',
        ],
        onUpdate: false
    )]
    public $nik;

    public function mount()
    {
        $confirmation = ClientConfirmationRepository::findBy([
            ['client_id', Auth::user()->client_id]
        ]);
        if ($confirmation) {
            return redirect()->route('login');
        }
        $this->name = UserRepository::find(Auth::id())->name;
        $this->claim_type = Client::CLAIM_TYPE_NORMAL;
    }

    public function updatedCurrentPosition($value)
    {
        if ($value === ClientConfirmation::CURRENT_POSITION_INDONESIA) {
            if ($this->back_to_jp_type === ClientConfirmation::BACK_TO_JP_TYPE_YES) {
                $this->estimated_return = null;
            } else {
                $this->estimated_return = null;
                $this->departure = null;
            }
        }
        if ($value === ClientConfirmation::CURRENT_POSITION_JEPANG) {
            $this->departure = null;
            $this->back_to_jp_type = null;
        }
    }

    public function updatedBackToJpType($value)
    {
        if ($value === ClientConfirmation::BACK_TO_JP_TYPE_YES) {
            $this->estimated_return = null;
        } else {
            $this->estimated_return = null;
            $this->departure = null;
        }
    }

    public function updated($a, $b)
    {

        consoleLog($this, [
            $this->current_position,
            $this->back_to_jp_type,
            $this->departure,

        ]);
        if (
            $this->current_position === ClientConfirmation::CURRENT_POSITION_INDONESIA &&
            $this->back_to_jp_type === ClientConfirmation::BACK_TO_JP_TYPE_YES &&
            $this->departure
        ) {
            $departure = Carbon::createFromFormat('Y-m', $this->departure);
            $is_less_then_7_month = $departure->greaterThanOrEqualTo(now()->addMonths(6));
            if (
                $is_less_then_7_month
            ) {
                $this->claim_type = 'ADJUSMNET';
            }
            if (
                !$is_less_then_7_month
            ) {
                $this->claim_type = 'NORMAL';
            }
        }

        if (
            $this->current_position === ClientConfirmation::CURRENT_POSITION_INDONESIA &&
            $this->back_to_jp_type === ClientConfirmation::BACK_TO_JP_TYPE_NO
        ) {
            $this->claim_type = 'SPEED';
        }
        if (
            $this->current_position === ClientConfirmation::CURRENT_POSITION_JEPANG
        ) {
            $this->claim_type = 'NORMAL';
        }
    }

    public function store()
    {
        $this->validate();

        $cleanNumber = ltrim(preg_replace('/[^0-9]/', '', (string)$this->phone_number), '0');

        if ($this->phone_country_code === '62') {
            if (strlen($cleanNumber) < 8 || strlen($cleanNumber) > 13) {
                $this->addError('phone_number', 'Nomor WhatsApp Indonesia harus berjumlah antara 8 hingga 13 digit.');
                return;
            }
        } elseif ($this->phone_country_code === '81') {
            // Hapus angka 0 di paling depan jika pengguna menyertakan format lokal (misal: 080... menjadi 80...)
            $numberToCheck = ltrim($cleanNumber, '0');

            // Validasi nomor seluler (60, 70, 80, 90) dan VoIP (50) dengan total 10 digit
            if (!preg_match('/^(?:50|[6789]0)\d{8}$/', $numberToCheck)) {
                $this->addError('phone_number', 'Nomor WhatsApp Jepang harus diawali dengan 50, 60, 70, 80, atau 90 dan berjumlah 10 digit.');
                return;
            }
        }

        $mergedPhone = NumberFormatter::formatForWhatsApp($this->phone_country_code . $cleanNumber);

        try {
            if (
                !$this->current_position
            ) {
                // throw new \Exception('Estimasi Kepulangan Harus Di isi');
                $this->addError(
                    'current_position',
                    'Posisi Saat Ini Harus Diisi.'
                );

                return;
            }
            if (
                $this->current_position === ClientConfirmation::CURRENT_POSITION_JEPANG &&
                !$this->estimated_return
            ) {
                // throw new \Exception('Estimasi Kepulangan Harus Di isi');
                $this->addError(
                    'estimated_return',
                    'Estimasi Kepulangan Harus Diisi.'
                );

                return;
            }
            if (
                $this->current_position === ClientConfirmation::CURRENT_POSITION_INDONESIA &&
                !$this->back_to_jp_type
            ) {
                // throw new \Exception('Rencara Kembali Harus Di isi');
                $this->addError(
                    'back_to_jp_type',
                    'Rencana Kembali Harus Diisi.'
                );

                return;
            }
            if (
                $this->current_position === ClientConfirmation::CURRENT_POSITION_INDONESIA &&
                $this->back_to_jp_type === ClientConfirmation::BACK_TO_JP_TYPE_YES &&
                !$this->departure
            ) {
                // throw new \Exception('Estimasi Kembali Ke Jepang Harus Di isi');
                $this->addError(
                    'departure',
                    'Estimasi Kembali Ke Jepang Harus Diisi.'
                );

                return;
            }
            DB::beginTransaction();

            $is_less_then_7_month = false;
            if ($this->departure) {

                $departure = Carbon::createFromFormat('Y-m', $this->departure);
                $is_less_then_7_month = $departure->greaterThanOrEqualTo(now()->addMonths(6));
            }

            $client_confirmation = ClientConfirmation::firstOrCreate([
                'client_id' => Auth::user()->client_id,
            ], [
                'client_id' => Auth::user()->client_id,
                'current_phone' => $mergedPhone,
                'nenkin_80_received_type' => $this->nenkin_80_received_type,
                'current_position' => $this->current_position,
                'back_to_jp_type' => $this->back_to_jp_type,
                'departure_month' => $this->departure ? Carbon::createFromFormat('Y-m', $this->departure)->format('m') : null,
                'departure_year' => $this->departure ? Carbon::createFromFormat('Y-m', $this->departure)->format('Y') : null,
                'estimated_return_month' => $this->estimated_return ? Carbon::createFromFormat('Y-m', $this->estimated_return)->format('m') : null,
                'estimated_return_year' => $this->estimated_return ? Carbon::createFromFormat('Y-m', $this->estimated_return)->format('Y') : null,
                'indonesia_address' => $this->indonesia_address,
                'nik' => $this->nik,
            ]);

            DB::commit();

            if (strtolower($this->claim_type) == Client::CLAIM_TYPE_NORMAL) {
                Alert::success($this, 'Berhasil', 'Tipe Pencairan Nenkin Anda Adalah ' . strtoupper($this->claim_type));
            } else {
                Alert::information($this, 'Data Berhasil Disimpan');
            }
            $this->dispatch('redirect-dashboard');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.client.form-confirmation');
    }
}

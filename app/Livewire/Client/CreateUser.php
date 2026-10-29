<?php

namespace App\Livewire\Client;

use App\Helpers\Alert;
use App\Models\Client\Client;
use App\Models\Client\ClientMagicLink;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\Client\ClientMagicLinkRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Component
{

    public $token;
    public $name;
    public $dob;
    public $tanggal;
    public $bulan;
    public $tahun;
    public $email;
    public $password_dob;

    public $is_verified = false;

    public function mount()
    {
        if (!$this->token) {
            return redirect()->route('login');
        }
        $magicLink = ClientMagicLinkRepository::findBy([
            ['token', $this->token]
        ]);
        if (!$magicLink) {
            return redirect()->route('login');
        }
        if ($magicLink->used_at && $magicLink->email) {
            return redirect()->route('login');
        }
    }

    public function verify()
    {

        $validated = $this->validate([
            'name' => ['required'],
            'tanggal' => ['required'],
            'bulan' => ['required'],
            'tahun' => ['required'],
        ], [
            'name.required' => 'Nama Lengkap Harus Diisi',
            'tanggal.required' => 'Tanggal Harus Diisi',
            'bulan.required' => 'Bulan Harus Diisi',
            'tahun.required' => 'Tahun Harus Diisi',
        ]);

        $this->dob = sprintf('%04d-%02d-%02d', $this->tahun, $this->bulan, $this->tanggal);

        $magicLink = ClientMagicLinkRepository::findBy([
            ['token', $this->token]
        ]);

        if (!$magicLink || !$magicLink->client) {
            Alert::fail($this, 'Register Gagal', 'Token tidak valid.');
            return;
        }

        $client = $magicLink->client;

        $nameMatched = mb_strtolower(trim($client->name)) === mb_strtolower(trim($this->name));
        $dobMatched = \Carbon\Carbon::parse($client->dob)->isSameDay(\Carbon\Carbon::parse($this->dob));

        if (! $nameMatched || ! $dobMatched) {
            Alert::fail($this, 'Register Gagal', 'Akun Tidak Sesuai, hubungi PIC');
            return;
        }

        $this->password_dob = Carbon::parse($client->dob)->format('dmY');
        // Save the array of data into the session
        session([
            'pending_magic_link_data' => [
                'token' => $this->token,
                'name'  => $this->name,
                'dob'   => $this->dob,
            ]
        ]);

        $this->is_verified = true;
    }

    public function registerWithGoogle()
    {
        return redirect()->route('auth.socialite.redirect', 'google');
    }

    public function store()
    {
        $validated = $this->validate([
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $allowedDomains = [
                        'gmail.com',
                        'outlook.com',
                        'hotmail.com',
                        'yahoo.com',
                        'yahoo.co.id',
                        'ymail.com',
                        'icloud.com',
                        'live.com'
                    ];

                    // Split by '@' and get the last part (the domain)
                    $parts = explode('@', $value);
                    $domain = end($parts);

                    // strtolower ensures it works even if they type "GMAIL.COM"
                    if (!in_array(strtolower($domain), $allowedDomains)) {
                        $domainText = $domain ?: 'tidak valid';
                        $fail("Domain email \"{$domainText}\" tidak diperbolehkan. Gunakan email yang valid !");
                    }
                }
            ],
        ], [
            'email.required' => 'Email Harus Diisi',
            'email.email' => 'Format Email Tidak Sesuai',
        ]);

        $client = ClientMagicLinkRepository::findBy([
            ['token', $this->token]
        ])->client;

        $nameMatched =
            mb_strtolower(trim($client->name))
            ===
            mb_strtolower(trim($this->name));

        $dobMatched = Carbon::parse($client->dob)
            ->isSameDay(Carbon::parse($this->dob));

        if (! $nameMatched || ! $dobMatched) {
            Alert::fail(
                $this,
                'Register Gagal',
                'Akun Tidak Sesuai, hubungi PIC'
            );

            return;
        }

        $user = UserRepository::findByEmail($this->email);
        if (!empty($user)) {
            Alert::fail($this, 'Register Gagal', 'Email Sudah Digunakan');
            return;
        }
        try {
            DB::beginTransaction();

            $magicLink = ClientMagicLinkRepository::findBy([
                ['token', $this->token]
            ]);

            $magicLink->update([
                'used_at' => now(),
                'email' => $this->email,
                'password' => $this->password_dob,
            ]);

            $user = UserRepository::create([
                'client_id' => $magicLink->client_id,
                'name' => $client->name,
                'no_whatsapp' => $magicLink->client->phone,
                'email' => $this->email,
                'password' => Hash::make($this->password_dob),
                'pic' => $magicLink->client->pic,
                'subject_id' => $magicLink->id,
                'subject_type' => ClientMagicLink::class,
            ]);

            $user->assignRole(User::ROLE_CLIENT);

            Auth::loginUsingId($user->id, true);
            $this->redirectRoute('login');

            DB::commit();

            Alert::information($this, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.client.create-user');
    }
}

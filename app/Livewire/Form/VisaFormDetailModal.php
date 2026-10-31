<?php

namespace App\Livewire\Form;

use App\Enums\Form\VisaFormStatus;
use App\Helpers\Alert;
use App\Models\Form\VisaForm;
use App\Models\MasterData\Regency;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class VisaFormDetailModal extends Component
{
    public bool $isOpen = false;

    public ?int $visaFormId = null;

    // Status & Metadata
    public string $status = 'DIAJUKAN';

    public ?string $pic_code = null;

    public ?string $nomor_registrasi = null;

    public ?string $catatan = null;

    // Step 1: Identitas Diri
    public string $nama_lengkap = '';

    public string $tempat_lahir = '';

    public ?string $tanggal_lahir = null;

    public ?string $jenis_kelamin = null;

    public ?string $status_kawin = null;

    public string $nik_ktp = '';

    public string $alamat_indonesia = '';

    public ?string $kode_pos_indonesia = null;

    public string $telepon_indonesia = '';

    public string $email_aktif = '';

    // Step 2: Data Paspor
    public string $nomor_paspor = '';

    public string $kantor_paspor = '';

    public ?string $tanggal_pengeluaran_paspor = null;

    public ?string $tanggal_habis_berlaku_paspor = null;

    // Step 3: COE
    public string $nomor_coe = '';

    public string $status_kependudukan_jepang = 'Specified Skilled Worker (SSW / Tokutei Ginou)';

    public string $lama_stay_jepang = '1 Tahun';

    // Step 4: Rencana Kedatangan & Kaisha di Jepang
    public ?string $tanggal_kedatangan = null;

    public string $bandara_tujuan = 'Haneda (HND)';

    public ?string $bandara_lainnya = null;

    public string $maskapai = '';

    public string $nama_kaisha = '';

    public string $alamat_jepang = '';

    public ?string $kode_pos_jepang = null;

    public string $telepon_jepang = '';

    public string $pernah_kerja_jepang = 'tidak';

    public ?string $periode_kerja_mulai = null;

    public ?string $periode_kerja_selesai = null;

    public string $nama_penanggung_jawab = '';

    public ?string $jenis_kelamin_penanggung_jawab = null;

    public string $alamat_penanggung_jawab = '';

    public string $telepon_penanggung_jawab = '';

    // Step 5: Berkas Lampiran
    public array $attachments = [];

    /**
     * Memuat daftar kabupaten/kota untuk komponen pencarian x-form.select-regency
     */
    public function loadRegencies(): array
    {
        return Regency::orderBy('name')->pluck('name')->toArray();
    }

    #[On('open-visa-form-detail')]
    public function open(int|string $id): void
    {
        $this->visaFormId = (int) $id;
        $visaForm = VisaForm::with('attachments')->findOrFail($this->visaFormId);

        $this->status = $visaForm->status instanceof VisaFormStatus ? $visaForm->status->value : (string) $visaForm->status;
        $this->pic_code = $visaForm->pic_code;
        $this->nomor_registrasi = $visaForm->nomor_registrasi;
        $this->catatan = $visaForm->catatan;

        $this->nama_lengkap = $visaForm->nama_lengkap ?? '';
        $this->tempat_lahir = $visaForm->tempat_lahir ?? '';
        $this->tanggal_lahir = $visaForm->tanggal_lahir ? Carbon::parse($visaForm->tanggal_lahir)->format('Y-m-d') : null;
        $this->jenis_kelamin = $visaForm->jenis_kelamin;
        $this->status_kawin = $visaForm->status_kawin;
        $this->nik_ktp = $visaForm->nik_ktp ?? '';
        $this->alamat_indonesia = $visaForm->alamat_indonesia ?? '';
        $this->kode_pos_indonesia = $visaForm->kode_pos_indonesia;
        $this->telepon_indonesia = (string) ($visaForm->telepon_indonesia ?? '');
        $this->email_aktif = $visaForm->email_aktif ?? '';

        $this->nomor_paspor = $visaForm->nomor_paspor ?? '';
        $this->kantor_paspor = $visaForm->kantor_paspor ?? '';
        $this->tanggal_pengeluaran_paspor = $visaForm->tanggal_pengeluaran_paspor ? Carbon::parse($visaForm->tanggal_pengeluaran_paspor)->format('Y-m-d') : null;
        $this->tanggal_habis_berlaku_paspor = $visaForm->tanggal_habis_berlaku_paspor ? Carbon::parse($visaForm->tanggal_habis_berlaku_paspor)->format('Y-m-d') : null;

        $this->nomor_coe = $visaForm->nomor_coe ?? '';
        $this->status_kependudukan_jepang = $visaForm->status_kependudukan_jepang ?? 'Specified Skilled Worker (SSW / Tokutei Ginou)';
        $this->lama_stay_jepang = $visaForm->lama_stay_jepang ?? '1 Tahun';

        $this->tanggal_kedatangan = $visaForm->tanggal_kedatangan ? Carbon::parse($visaForm->tanggal_kedatangan)->format('Y-m-d') : null;
        $this->bandara_tujuan = $visaForm->bandara_tujuan ?? 'Haneda (HND)';
        $this->bandara_lainnya = $visaForm->bandara_lainnya;
        $this->maskapai = $visaForm->maskapai ?? '';
        $this->nama_kaisha = $visaForm->nama_kaisha ?? '';
        $this->alamat_jepang = $visaForm->alamat_jepang ?? '';
        $this->kode_pos_jepang = $visaForm->kode_pos_jepang;
        $this->telepon_jepang = (string) ($visaForm->telepon_jepang ?? '');

        // Normalisasi pernah kerja jepang ('ya', 'pernah' => 'pernah')
        $rawPernah = strtolower((string) ($visaForm->pernah_kerja_jepang ?? ''));
        $this->pernah_kerja_jepang = in_array($rawPernah, ['pernah', 'ya', '1', 'true'], true) ? 'pernah' : 'tidak';

        // Format periode kerja ke YYYY-MM untuk input type month
        if ($visaForm->periode_kerja_mulai && preg_match('/^\d{4}-\d{2}/', $visaForm->periode_kerja_mulai, $m)) {
            $this->periode_kerja_mulai = $m[0];
        } else {
            $this->periode_kerja_mulai = $visaForm->periode_kerja_mulai;
        }

        if ($visaForm->periode_kerja_selesai && preg_match('/^\d{4}-\d{2}/', $visaForm->periode_kerja_selesai, $m)) {
            $this->periode_kerja_selesai = $m[0];
        } else {
            $this->periode_kerja_selesai = $visaForm->periode_kerja_selesai;
        }

        $this->nama_penanggung_jawab = $visaForm->nama_penanggung_jawab ?? '';
        $this->jenis_kelamin_penanggung_jawab = $visaForm->jenis_kelamin_penanggung_jawab;
        $this->alamat_penanggung_jawab = $visaForm->alamat_penanggung_jawab ?? '';
        $this->telepon_penanggung_jawab = (string) ($visaForm->telepon_penanggung_jawab ?? '');

        $this->attachments = $visaForm->attachments ? $visaForm->attachments->toArray() : [];
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'status' => ['required', Rule::in(array_keys(VisaFormStatus::options()))],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'status_kawin' => ['required', Rule::in(['belum kawin', 'kawin', 'janda/duda', 'cerai'])],
            'nik_ktp' => ['required', 'string', 'max:20'],
            'alamat_indonesia' => ['required', 'string'],
            'telepon_indonesia' => ['required', 'string', 'max:30', 'regex:/^[0-9\-]+$/'],
            'email_aktif' => ['required', 'email', 'max:255'],
            'nomor_paspor' => ['required', 'string', 'max:50'],
            'kantor_paspor' => ['required', 'string', 'max:100'],
            'tanggal_pengeluaran_paspor' => ['required', 'date'],
            'tanggal_habis_berlaku_paspor' => ['required', 'date'],
            'nomor_coe' => ['required', 'string', 'max:50'],
            'tanggal_kedatangan' => ['required', 'date'],
            'nama_kaisha' => ['required', 'string', 'max:255'],
            'alamat_jepang' => ['required', 'string'],
            'telepon_jepang' => ['required', 'string', 'max:30', 'regex:/^[0-9\-]+$/'],
            'nama_penanggung_jawab' => ['required', 'string', 'max:255'],
            'jenis_kelamin_penanggung_jawab' => ['required', Rule::in(['L', 'P'])],
            'alamat_penanggung_jawab' => ['required', 'string', 'max:255'],
            'telepon_penanggung_jawab' => ['required', 'string', 'max:30', 'regex:/^[0-9\-]+$/'],
        ], [
            'telepon_indonesia.regex' => 'Nomor telepon Indonesia hanya boleh memuat angka dan tanda minus (-).',
            'telepon_jepang.regex' => 'Nomor telepon Jepang hanya boleh memuat angka dan tanda minus (-).',
            'telepon_penanggung_jawab.regex' => 'Nomor telepon penanggung jawab hanya boleh memuat angka dan tanda minus (-).',
        ]);

        $visaForm = VisaForm::findOrFail($this->visaFormId);
        $visaForm->update([
            'status' => $this->status,
            'pic_code' => $this->pic_code,
            'catatan' => $this->catatan,
            'nama_lengkap' => $this->nama_lengkap,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'status_kawin' => $this->status_kawin,
            'nik_ktp' => $this->nik_ktp,
            'alamat_indonesia' => $this->alamat_indonesia,
            'kode_pos_indonesia' => $this->kode_pos_indonesia,
            'telepon_indonesia' => $this->telepon_indonesia,
            'email_aktif' => $this->email_aktif,
            'nomor_paspor' => $this->nomor_paspor,
            'kantor_paspor' => $this->kantor_paspor,
            'tanggal_pengeluaran_paspor' => $this->tanggal_pengeluaran_paspor,
            'tanggal_habis_berlaku_paspor' => $this->tanggal_habis_berlaku_paspor,
            'nomor_coe' => $this->nomor_coe,
            'status_kependudukan_jepang' => $this->status_kependudukan_jepang,
            'lama_stay_jepang' => $this->lama_stay_jepang,
            'tanggal_kedatangan' => $this->tanggal_kedatangan,
            'bandara_tujuan' => $this->bandara_tujuan,
            'bandara_lainnya' => $this->bandara_tujuan === 'Lainnya' ? $this->bandara_lainnya : null,
            'maskapai' => $this->maskapai,
            'nama_kaisha' => $this->nama_kaisha,
            'alamat_jepang' => $this->alamat_jepang,
            'kode_pos_jepang' => $this->kode_pos_jepang,
            'telepon_jepang' => $this->telepon_jepang,
            'pernah_kerja_jepang' => $this->pernah_kerja_jepang,
            'periode_kerja_mulai' => $this->pernah_kerja_jepang === 'pernah' ? $this->periode_kerja_mulai : null,
            'periode_kerja_selesai' => $this->pernah_kerja_jepang === 'pernah' ? $this->periode_kerja_selesai : null,
            'nama_penanggung_jawab' => $this->nama_penanggung_jawab,
            'jenis_kelamin_penanggung_jawab' => $this->jenis_kelamin_penanggung_jawab,
            'alamat_penanggung_jawab' => $this->alamat_penanggung_jawab,
            'telepon_penanggung_jawab' => $this->telepon_penanggung_jawab,
        ]);

        $this->isOpen = false;
        $this->dispatch('datatable-refresh');
        Alert::information($this, 'Data formulir visa berhasil diperbarui');
    }

    public function render()
    {
        return view('livewire.form.visa-form-detail-modal');
    }
}

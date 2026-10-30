<?php

namespace App\Livewire\Exata\ExataFormCandidate;

use App\Helpers\Alert;
use App\Helpers\AppCrypt;
use App\Helpers\FilePathHelper;
use App\Models\Exata\Exata;
use App\Models\Exata\ExataFormCandidate;
use App\Repositories\Exata\ExataCurriculumVitaeRepository;
use App\Repositories\Exata\ExataFormCandidateRepository;
use App\Repositories\Exata\ExataJapaneseLanguageCertificateRepository;
use App\Repositories\Exata\ExataRepository;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public $objId;

    public $exata_id;

    public $candidate_profile;

    // Form J-Expert
    #[Validate('required', message: 'Nama lengkap harus diisi', onUpdate: false)]
    public $NamaLengkap;

    #[Validate('required', message: 'Tanggal lahir harus diisi', onUpdate: false)]
    public $TanggalLahir;

    #[Validate('required', message: 'Gender harus diisi', onUpdate: false)]
    public $Gender;

    #[Validate('required', message: 'Pendidikan harus diisi', onUpdate: false)]
    public $Pendidikan;

    #[Validate('required', message: 'Level bahasa harus diisi', onUpdate: false)]
    public $LevelBahasa;

    #[Validate('required', message: 'Tahun terbit harus diisi', onUpdate: false)]
    public $TahunTerbit;

    #[Validate('required', message: 'Lama di Jepang harus diisi', onUpdate: false)]
    public $LamaDiJepang;

    #[Validate('required', message: 'Tanggal pulang harus diisi', onUpdate: false)]
    public $TanggalPulang;

    public $Sensei;

    public $Dokumen;

    public $Penerjemah;

    #[Validate('required', message: 'Estimasi gaji harus diisi', onUpdate: false)]
    public $EstimasiGaji;

    #[Validate('required', message: 'Domisili harus diisi', onUpdate: false)]
    public $Domisili;

    #[Validate('required', message: 'Penempatan kerja harus diisi', onUpdate: false)]
    public $Penempatankerja;

    #[Validate('required', message: 'Tanggal siap kerja harus diisi', onUpdate: false)]
    public $TglSiapkerja;

    #[Validate('required', message: 'Bidang kerja di Jepang harus diisi', onUpdate: false)]
    public $BidangKerjadiJepang;

    #[Validate('required', message: 'Bidang kerja pilihan harus diisi', onUpdate: false)]
    public $BidangKerjaPilihan;

    public $Senmongkyu;

    public $BidangSenmongkyu;

    public $JenisVisa;

    #[Validate('required', message: 'Provinsi harus diisi', onUpdate: false)]
    public $Provinsi;

    #[Validate('required', message: 'Kota harus diisi', onUpdate: false)]
    public $Kota;

    #[Validate('required', message: 'Nama TikTok harus diisi', onUpdate: false)]
    public $NamaTikTok;

    #[Validate('required', message: 'Nama Instagram harus diisi', onUpdate: false)]
    public $NamaInstagram;

    #[Validate('required', message: 'No Telp Indonesia harus diisi', onUpdate: false)]
    public $NoTelpIndonesia;

    public $NoTelpJepang;

    #[Validate('required|email', message: 'Email harus diisi dengan format benar', onUpdate: false)]
    public $Email;

    #[Validate('required', message: 'Nama LPK harus diisi', onUpdate: false)]
    public $NamaLPK;

    #[Validate('required', message: 'Tinggi badan harus diisi', onUpdate: false)]
    public $tinggi_badan;

    #[Validate('required', message: 'Berat badan harus diisi', onUpdate: false)]
    public $berat_badan;

    #[Validate('required', message: 'Skill bahasa lain harus diisi', onUpdate: false)]
    public $skill_bahasa_lain;

    #[Validate('required', message: 'Skill komputer harus diisi', onUpdate: false)]
    public $skill_komputer;

    #[Validate('required', message: 'Pencapaian tertinggi harus diisi', onUpdate: false)]
    public $pencapaian_tertinggi;

    #[Validate('required', message: 'Value saat di Jepang harus diisi', onUpdate: false)]
    public $value_saat_di_jepang;

    #[Validate('required', message: 'Soft skill harus diisi', onUpdate: false)]
    public $soft_skill;

    #[Validate('required', message: 'Skill lainnya harus diisi', onUpdate: false)]
    public $skill_lainnya;

    #[Validate('required', message: 'Pengalaman kerja harus diisi', onUpdate: false)]
    public $pengalaman_kerja;

    public $sertifikat_bahasa_jepang = [];

    public $cv = [];

    public $sertifikat_bahasa_jepang_old = [];

    public $cv_old = [];

    public $sertifikat_bahasa_jepang_removes = [];

    public $cv_removes = [];

    public $password;

    public $input_password;

    public $max_attempts = 3;

    public $authorized = false;

    protected function rules()
    {
        return [
            'cv' => [
                function ($attribute, $value, $fail) {
                    if (empty($this->cv) && empty($this->cv_old)) {
                        $fail('CV harus diisi');
                    }
                },
            ],
        ];
    }

    public function mount()
    {
        try {
            $form_id = AppCrypt::decrypt($this->objId);
            if (! $form_id) {
                abort(404, 'Link tidak valid atau telah dimanipulasi.');
            }
            $form = ExataFormCandidateRepository::find($form_id);
            if (! $form) {
                abort(404, 'Form Tidak Tersedia');
            }
            if ($form->expired_at && now()->greaterThan($form->expired_at)) {
                ExataFormCandidateRepository::update(
                    $form_id,
                    ['status' => ExataFormCandidate::STATUS_EXPIRED]
                );
                abort(403, 'Form sudah expired');
            }
            if ($form->exata_id && $form->status == ExataFormCandidate::STATUS_SUBMITTED) {
                abort(403, 'Form sudah expired');
            }
            $this->password = $form->password;
        } catch (DecryptException $e) {
            abort(404, 'Form tidak tersedia');
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        return redirect()->route('exata_form_candidate.form', $this->objId);
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        return redirect()->route('exata_form_candidate.form', $this->objId);
    }

    public function checkPassword()
    {
        try {

            $form_id = AppCrypt::decrypt($this->objId);
            if (! $form_id) {
                abort(404, 'Link tidak valid atau telah dimanipulasi.');
            }
            $form = ExataFormCandidateRepository::find($form_id);

            if (! $form) {
                abort(404, 'Form tidak ditemukan');
            }

            // ✅ check expired first
            if ($form->expired_at && now()->greaterThan($form->expired_at)) {
                abort(403, 'Form sudah expired');
            }

            // ✅ password validation
            if ($this->input_password !== $form->password) {

                $this->max_attempts--;

                Alert::fail(
                    $this,
                    'Password Salah',
                    "Sisa percobaan: {$this->max_attempts}"
                );

                // lock form when attempts finished
                if ($this->max_attempts <= 0) {

                    ExataFormCandidateRepository::update(
                        $form_id,
                        ['expired_at' => now()]
                    );

                    return redirect()->route(
                        'exata_form_candidate.form',
                        $this->objId
                    );
                }

                return;
            }

            // ✅ SUCCESS ACCESS
            $this->authorized = true;

            Alert::success($this, 'Berhasil', 'Password benar');
            $this->dispatch('onAuthorized');
        } catch (DecryptException $e) {
            abort(404, 'Form tidak tersedia');
        }
    }

    public function removeSertifikatBahasaJepang($index)
    {
        unset($this->sertifikat_bahasa_jepang[$index]);

        // IMPORTANT → reset index array
        $this->sertifikat_bahasa_jepang = array_values($this->sertifikat_bahasa_jepang);
    }

    public function removeCV($index)
    {
        unset($this->cv[$index]);

        // IMPORTANT → reset index array
        $this->cv = array_values($this->cv);
    }

    public function removeSertifikatBahasaJepangOld($index)
    {

        if ($this->sertifikat_bahasa_jepang_old[$index]['id']) {
            $this->sertifikat_bahasa_jepang_removes[] = $this->sertifikat_bahasa_jepang_old[$index]['id'];
        }
        unset($this->sertifikat_bahasa_jepang_old[$index]);
    }

    public function removeCVOld($index)
    {

        if ($this->cv_old[$index]['id']) {
            $this->cv_removes[] = $this->cv_old[$index]['id'];
        }
        unset($this->cv_old[$index]);
    }

    public function selectDomisili($data)
    {
        $this->Domisili[] = $data['id'];
    }

    public function unSelectDomisili($data)
    {
        $index = array_search($data['id'], $this->Domisili);
        if ($index !== false) {
            unset($this->Domisili[$index]);
        }
    }

    public function selectPreferensiLokasi($data)
    {
        $this->Penempatankerja[] = $data['id'];
    }

    public function unSelectPreferensiLokasi($data)
    {
        $index = array_search($data['id'], $this->Penempatankerja);
        if ($index !== false) {
            unset($this->Penempatankerja[$index]);
        }
    }

    public function selectBidangKerjaPilihan($data)
    {
        $this->BidangKerjaPilihan[] = $data['id'];
    }

    public function unSelectBidangKerjaPilihan($data)
    {
        $index = array_search($data['id'], $this->BidangKerjaPilihan);
        if ($index !== false) {
            unset($this->BidangKerjaPilihan[$index]);
        }
    }

    public function store()
    {
        $this->validate();
        try {
            DB::transaction(function () {
                // Form Candidate
                $validateData = [
                    // Form J-Expert
                    'NamaLengkap' => $this->NamaLengkap,
                    'TanggalLahir' => $this->TanggalLahir,
                    'Gender' => $this->Gender,
                    'Pendidikan' => $this->Pendidikan,
                    'LevelBahasa' => $this->LevelBahasa,
                    'TahunTerbit' => $this->TahunTerbit,
                    'LamaDiJepang' => $this->LamaDiJepang,
                    'TanggalPulang' => $this->TanggalPulang,
                    'Sensei' => $this->Sensei ? 'YA' : 'TIDAK',
                    'Dokumen' => $this->Dokumen ? 'YA' : 'TIDAK',
                    'Penerjemah' => $this->Penerjemah ? 'YA' : 'TIDAK',
                    'EstimasiGaji' => $this->EstimasiGaji,
                    'Domisili' => $this->Domisili ? $this->Domisili[0] : null,
                    'Penempatankerja' => $this->Penempatankerja ? implode(',', $this->Penempatankerja) : null,
                    'TglSiapkerja' => $this->TglSiapkerja,
                    'BidangKerjadiJepang' => $this->BidangKerjadiJepang,
                    'BidangKerjaPilihan' => $this->BidangKerjaPilihan ? implode(',', $this->BidangKerjaPilihan) : null,
                    'Senmongkyu' => $this->Senmongkyu ? 'YA' : 'TIDAK',
                    'BidangSenmongkyu' => $this->BidangSenmongkyu,
                    'JenisVisa' => $this->JenisVisa,
                    'Provinsi' => $this->Provinsi,
                    'Kota' => $this->Kota,
                    'NamaTikTok' => $this->NamaTikTok,
                    'NamaInstagram' => $this->NamaInstagram,
                    'NoTelpIndonesia' => $this->NoTelpIndonesia,
                    'NoTelpJepang' => $this->NoTelpJepang,
                    'Email' => $this->Email,
                    'NamaLPK' => $this->NamaLPK,

                    'TinggiBadan' => $this->tinggi_badan ? $this->tinggi_badan : null,
                    'BeratBadan' => $this->berat_badan ? $this->berat_badan : null,
                    'SkillBahasaLain' => $this->skill_bahasa_lain,
                    'SkillKomputer' => $this->skill_komputer,
                    'PencapaianTertinggi' => $this->pencapaian_tertinggi,
                    'ValueSaatDiJepang' => $this->value_saat_di_jepang,
                    'SoftSkill' => $this->soft_skill,
                    'SkillLainnya' => $this->skill_lainnya,
                    'PengalamanKerja' => $this->pengalaman_kerja,
                    'Pipeline' => Exata::FILTER_PIPELINE_CHOICE[Exata::PIPELINE_NEW_LEAD],
                    'TglInput' => now(),
                    'PICSales' => Exata::FILTER_SALES_FORM_KANDIDAT,
                ];
                $exata = ExataRepository::create($validateData);
                $exata_id = $exata->id;
                foreach ($this->sertifikat_bahasa_jepang as $sertifikat) {

                    $path = $sertifikat->store(FilePathHelper::FILE_CANDIDATE_JAPANESE_LANGUAGE_CERTIFICATE, 'public');
                    $validatedSertifikat = [
                        'exata_id' => $exata_id,
                        'file' => $path,
                        'name' => $sertifikat->getClientOriginalName(),
                    ];

                    ExataJapaneseLanguageCertificateRepository::create($validatedSertifikat);
                }
                foreach ($this->cv as $cv) {

                    $path = $cv->store(FilePathHelper::FILE_CANDIDATE_CURRICULUM_VITAE, 'public');
                    $validatedCv = [
                        'exata_id' => $exata_id,
                        'file' => $path,
                        'name' => $cv->getClientOriginalName(),
                    ];

                    ExataCurriculumVitaeRepository::create($validatedCv);
                }

                $id = AppCrypt::decrypt($this->objId);
                if (! $id) {
                    abort(404, 'Link tidak valid atau telah dimanipulasi.');
                }
                ExataFormCandidateRepository::update(
                    $id,
                    [
                        'status' => ExataFormCandidate::STATUS_SUBMITTED,
                        'exata_id' => $exata_id,
                    ]
                );
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
        return view('livewire.exata.exata-form-candidate.form');
    }
}

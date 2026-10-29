<?php

namespace App\Livewire\Client;

use App\Models\Document;
use App\Models\UpodSubmission;
use App\Models\UpodSurveyResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SatisfactionSurvey extends Component
{
    public $step = 1;

    public $responses = [
        'q1' => '',
        'q2' => '',
        'q3' => '',
        'q4' => '',
        'q5' => '',
        'q6' => '',
        'q7' => '',
        'q8' => '',
        'q9' => '',
        'q10' => '',
    ];

    public $hasSubmitted = false;

    public $showSurvey = false;

    public function mount()
    {
        $user = Auth::user();
        if ($user && $user->client_id) {
            $client = $user->client;
            if ($client) {
                // Check submission guard
                $this->hasSubmitted = UpodSubmission::where('client_id', $client->id)->exists();

                // Check document requirement (ichijikin, resi, kwitansi)
                $hasAllDocs = Document::where('client_id', $client->id)
                    ->whereIn('type', ['ichijikin', 'resi', 'kwitansi'])
                    ->distinct('type')
                    ->count('type') === 3;

                // Resolved UPOD type must be satisfaction_survey
                $isSurveyType = $client->resolvedUpodType() === 'satisfaction_survey';

                if (! $this->hasSubmitted && $hasAllDocs && $isSurveyType) {
                    $this->showSurvey = true;
                }
            }
        }
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->step === 4) {
            if ($this->responses['q4'] === 'Ya') {
                $this->step = 5; // Go to Q5/Q6
            } else {
                $this->step = 7; // Skip Q5/Q6 to Q7
            }
        } elseif ($this->step === 6) {
            $this->step = 7;
        } else {
            $this->step++;
        }
    }

    public function prevStep()
    {
        if ($this->step === 7) {
            if ($this->responses['q4'] === 'Ya') {
                $this->step = 6;
            } else {
                $this->step = 4;
            }
        } elseif ($this->step === 5) {
            $this->step = 4;
        } else {
            $this->step--;
        }
    }

    protected function validateCurrentStep()
    {
        $rules = [];
        $messages = [];

        switch ($this->step) {
            case 1:
                $rules['responses.q1'] = 'required';
                $messages['responses.q1.required'] = 'Harap jawab pertanyaan nomor 1.';
                break;
            case 2:
                $rules['responses.q2'] = 'required';
                $messages['responses.q2.required'] = 'Harap jawab pertanyaan nomor 2.';
                break;
            case 3:
                $rules['responses.q3'] = 'required';
                $messages['responses.q3.required'] = 'Harap jawab pertanyaan nomor 3.';
                break;
            case 4:
                $rules['responses.q4'] = 'required';
                $messages['responses.q4.required'] = 'Harap jawab pertanyaan nomor 4.';
                break;
            case 5:
                $rules['responses.q5'] = 'required';
                $messages['responses.q5.required'] = 'Harap isi alasan rekomendasi Anda.';
                break;
            case 6:
                $rules['responses.q6'] = 'required';
                $messages['responses.q6.required'] = 'Harap isi saran perbaikan Anda.';
                break;
            case 7:
                $rules['responses.q7'] = 'required';
                $messages['responses.q7.required'] = 'Harap jawab pertanyaan nomor 7.';
                break;
            case 8:
                $rules['responses.q8'] = 'required';
                $messages['responses.q8.required'] = 'Harap jawab pertanyaan nomor 8.';
                break;
            case 9:
                $rules['responses.q9'] = 'required';
                $messages['responses.q9.required'] = 'Harap jawab pertanyaan nomor 9.';
                break;
            case 10:
                $rules['responses.q10'] = 'required';
                $messages['responses.q10.required'] = 'Harap jawab pertanyaan nomor 10.';
                break;
        }

        $this->validate($rules, $messages);
    }

    public function submit()
    {
        $this->validateCurrentStep();

        $user = Auth::user();
        if (! $user || ! $user->client_id) {
            return;
        }

        $client = $user->client;
        if (! $client) {
            return;
        }

        // Validate all based on Q4
        $rules = [
            'responses.q1' => 'required',
            'responses.q2' => 'required',
            'responses.q3' => 'required',
            'responses.q4' => 'required',
            'responses.q7' => 'required',
            'responses.q8' => 'required',
            'responses.q9' => 'required',
            'responses.q10' => 'required',
        ];

        if ($this->responses['q4'] === 'Ya') {
            $rules['responses.q5'] = 'required';
            $rules['responses.q6'] = 'required';
        }

        $this->validate($rules);

        // Save Response
        UpodSurveyResponse::create([
            'client_id' => $client->id,
            'responses' => $this->responses,
            'submitted_at' => now(),
        ]);

        // Save Submission Guard
        UpodSubmission::create([
            'client_id' => $client->id,
            'upod_type' => 'satisfaction_survey',
            'submitted_at' => now(),
        ]);

        $this->hasSubmitted = true;
        $this->showSurvey = false;

        $this->dispatch('SwalInformation', [
            'success',
            'Survei Terkirim',
            'Terima kasih atas partisipasi Anda dalam survei kepuasan pelanggan.',
        ]);

        $this->dispatch('refresh-survey');
    }

    public function render()
    {
        return view('livewire.client.satisfaction-survey');
    }
}

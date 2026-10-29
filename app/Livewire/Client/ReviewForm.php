<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Document;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Support\Facades\Auth;

class ReviewForm extends Component
{
    public $rating = 0;
    public $message = '';
    public $hasSubmitted = false;
    public $submittedMessage = '';
    public $redirectUrl = null;
    public bool $popupMode = false;

    public function mount(bool $popupMode = false)
    {
        $this->popupMode = $popupMode;

        $user = Auth::user();
        if ($user && $user->client_id) {
            $this->hasSubmitted = Review::where('client_id', $user->client_id)->exists();
            if ($this->hasSubmitted) {
                $this->submittedMessage = "Terima kasih, ulasan Anda sudah kami terima.";
            }
        }
    }

    public function selectRating($value)
    {
        $this->rating = (int) $value;
    }

    public function submit(ReviewService $reviewService)
    {
        $user = Auth::user();
        if (!$user || !$user->client_id) {
            return;
        }

        $client = $user->client;
        if (!$client) {
            return;
        }

        // Validate rating is chosen
        if ($this->rating < 1 || $this->rating > 5) {
            $this->addError('rating', 'Harap pilih rating terlebih dahulu.');
            return;
        }

        // Double check documents availability
        $hasAllDocuments = Document::where('client_id', $client->id)
            ->whereIn('type', ['ichijikin', 'resi', 'kwitansi'])
            ->distinct('type')
            ->count('type') === 3;

        if (!$hasAllDocuments) {
            $this->addError('general', 'Form ulasan belum tersedia karena dokumen Anda belum lengkap.');
            return;
        }

        // Double check unique constraint
        if (Review::where('client_id', $client->id)->exists()) {
            $this->hasSubmitted = true;
            $this->submittedMessage = "Terima kasih, ulasan Anda sudah kami terima.";
            return;
        }

        // Submit review using Service
        $result = $reviewService->submitReview($client, $this->rating, $this->message);

        // Record UPOD submission guard
        \App\Models\UpodSubmission::updateOrCreate(
            ['client_id' => $client->id],
            [
                'upod_type' => 'google_review',
                'submitted_at' => now(),
            ]
        );

        $this->hasSubmitted = true;
        $this->submittedMessage = $result['message'];
        $this->redirectUrl = $result['redirect_url'];

        // Dispatch sweetalert or event using standard alert structure
        $this->dispatch('SwalInformation', [
            'success',
            'Ulasan Terkirim',
            $result['message']
        ]);

        if (!empty($this->redirectUrl)) {
            $this->dispatch('open-new-tab', ['url' => $this->redirectUrl]);
        }
    }

    public function render()
    {
        $user = Auth::user();
        $client = $user ? $user->client : null;
        
        $showForm = false;
        $hasAllDocuments = false;

        if ($client) {
            $hasAllDocuments = Document::where('client_id', $client->id)
                ->whereIn('type', ['ichijikin', 'resi', 'kwitansi'])
                ->distinct('type')
                ->count('type') === 3;

            // Form review hanya muncul jika ketiga dokumen sudah ada
            if ($hasAllDocuments) {
                $showForm = true;
            }
        }

        return view('livewire.client.review-form', [
            'showForm'       => $showForm,
            'hasAllDocuments' => $hasAllDocuments,
            'popupMode'      => $this->popupMode,
        ]);
    }
}

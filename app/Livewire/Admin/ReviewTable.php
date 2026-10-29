<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReviewTable extends Component
{
    use WithPagination;

    public $statusFilter = 'all'; // all, intercepted, resolved
    public $selectedReviewId;
    public $notes = '';
    public $isModalOpen = false;

    protected $queryString = [
        'statusFilter' => [
            'except' => 'all',
            'as' => 'status'
        ],
    ];

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openResolveModal($reviewId)
    {
        $this->selectedReviewId = $reviewId;
        $this->notes = '';
        $this->isModalOpen = true;
    }

    public function closeResolveModal()
    {
        $this->isModalOpen = false;
        $this->selectedReviewId = null;
        $this->notes = '';
    }

    public function resolveReview()
    {
        if (!$this->selectedReviewId) {
            return;
        }

        $review = Review::findOrFail($this->selectedReviewId);

        if ($review->status !== 'intercepted') {
            $this->closeResolveModal();
            return;
        }

        $review->update([
            'status' => 'resolved',
            'resolved_by' => Auth::id(),
            'resolved_at' => Carbon::now('Asia/Jakarta'),
            'notes' => $this->notes,
        ]);

        $this->closeResolveModal();

        $this->dispatch('SwalInformation', [
            'success',
            'Ulasan Di-resolve',
            'Ulasan berhasil ditandai sebagai resolved.'
        ]);
    }

    public function render()
    {
        // Get stats
        $stats = [
            'total' => Review::count(),
            'intercepted' => Review::where('status', 'intercepted')->count(),
            'resolved' => Review::where('status', 'resolved')->count(),
            'forwarded' => Review::where('status', 'forwarded')->count(),
        ];

        // Query reviews
        $query = Review::with(['client', 'resolver'])
            ->orderBy('submitted_at', 'desc');

        if ($this->statusFilter === 'intercepted') {
            $query->where('status', 'intercepted');
        } elseif ($this->statusFilter === 'resolved') {
            $query->where('status', 'resolved');
        }

        $reviews = $query->paginate(10);

        return view('livewire.admin.review-table', [
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }
}

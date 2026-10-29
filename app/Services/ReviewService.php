<?php

namespace App\Services;

use App\Models\Client\Client;
use App\Models\Review;
use Carbon\Carbon;

class ReviewService
{
    /**
     * Submit a review for a client.
     *
     * @param  Client  $client
     */
    public function submitReview($client, int $rating, ?string $message = null): array
    {
        // Intercept rating 1-3, forward rating 4-5
        $status = ($rating >= 1 && $rating <= 3) ? 'intercepted' : 'forwarded';

        $review = Review::create([
            'client_id' => $client->id,
            'rating' => $rating,
            'message' => $message,
            'status' => $status,
            'submitted_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $result = [
            'review' => $review,
            'status' => $status,
            'message' => '',
            'redirect_url' => null,
        ];

        if ($status === 'intercepted') {
            $adminWaNumber = config('exata.admin_wa_number');

            // Format: Review dari {nama_klien} | Rating: {rating}/5 | Pesan: {pesan}
            $text = "Review dari {$client->name} | Rating: {$rating}/5 | Pesan: ".($message ?? '-');
            $urlEncodedText = rawurlencode($text);

            $result['redirect_url'] = "https://wa.me/{$adminWaNumber}?text={$urlEncodedText}";
            $result['message'] = 'Terima kasih atas masukan Anda. Tim kami akan menghubungi Anda segera.';
        } else {
            $result['redirect_url'] = env('GOOGLE_REVIEW_URL', '');
            $result['message'] = 'Terima kasih! Anda akan diarahkan ke halaman Google Review.';
        }

        return $result;
    }
}

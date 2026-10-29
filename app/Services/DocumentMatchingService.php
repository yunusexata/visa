<?php

namespace App\Services;

use App\Models\Client\Client;

class DocumentMatchingService
{
    /**
     * Cache of normalized client names to avoid redundant normalizations.
     */
    private array $normalizedClientsCache = [];

    /**
     * Normalize a given string (filename or client name).
     */
    public function normalize(string $name, bool $isFilename = false): string
    {
        if ($isFilename) {
            // Remove extension first
            $name = pathinfo($name, PATHINFO_FILENAME);
        }

        // Lowercase all characters
        $name = mb_strtolower($name, 'UTF-8');

        // Replace underscore, dash, dot with space
        $name = str_replace(['_', '-', '.'], ' ', $name);

        // Trim and replace multiple spaces with single space
        return preg_replace('/\s+/', ' ', trim($name));
    }

    /**
     * Extract only the candidate client name from a given filename based on its document type.
     */
    private function extractNameFromFilename(string $filename, string $type): string
    {
        $filenameNoExt = pathinfo($filename, PATHINFO_FILENAME);
        
        // Split filename by underscores or spaces using preg_split
        $tokens = preg_split('/[_\s]+/', $filenameNoExt);
        $tokens = array_filter($tokens); // remove any empty tokens

        if (empty($tokens)) {
            return '';
        }

        // Re-index array values to ensure contiguous numerical keys
        $tokens = array_values($tokens);

        switch (strtolower($type)) {
            case 'ichijikin':
                // Pola Baru: {NAMA LENGKAP}_{NO NENKIN}.jpg (contoh: AAN ULIN NUHA_3858043907.jpg)
                // Pola Lama: {angka}_{NAMA LENGKAP}.jpg (contoh: 123_Budi Santoso.jpg)
                
                // 1. Hapus token pertama jika berupa angka murni (pola lama)
                if (ctype_digit($tokens[0]) || preg_match('/^\d+$/', $tokens[0])) {
                    array_shift($tokens);
                }
                
                // 2. Hapus token terakhir jika berupa angka murni (pola baru / nomor nenkin)
                if (!empty($tokens)) {
                    $last = end($tokens);
                    if (ctype_digit($last) || preg_match('/^\d+$/', $last)) {
                        array_pop($tokens);
                    }
                }
                break;

            case 'resi':
                // Pola Baru: [PREFIX/BATCH]_[BANK]_[NAMA LENGKAP]_[NOMOR].jpg
                // Contoh: SPEED JUN 26 KL2_BRI_YUDA MAREDO MANULLANG_736301024206536.jpg
                // Pisahkan berdasarkan underscore (_) untuk menjaga integritas nama lengkap
                $parts = explode('_', $filenameNoExt);
                $parts = array_filter(array_map('trim', $parts));
                $parts = array_values($parts);
                
                if (!empty($parts)) {
                    // 1. Hapus bagian terakhir jika berupa angka murni (nomor transaksi/rekening)
                    $last = end($parts);
                    if (ctype_digit($last) || preg_match('/^\d+$/', $last)) {
                        array_pop($parts);
                    }
                }
                
                if (!empty($parts)) {
                    // 2. Cari separator BANK. Bank adalah token yang cocok dengan list bank indonesia
                    $banks = ['bri', 'bca', 'bni', 'mandiri', 'bsi', 'danamon', 'cimb', 'permata', 'btpn', 'panin', 'ocbc', 'maybank', 'jago', 'sea'];
                    $bankIndex = null;
                    foreach ($parts as $index => $part) {
                        if (in_array(strtolower($part), $banks)) {
                            $bankIndex = $index;
                            break;
                        }
                    }
                    
                    if ($bankIndex !== null && $bankIndex < count($parts) - 1) {
                        // Jika ditemukan bank, ambil semua bagian setelah bank
                        $parts = array_slice($parts, $bankIndex + 1);
                    } else {
                        // Fallback: jika tidak ada bank, ambil bagian terakhir (kemungkinan besar adalah NAMA LENGKAP)
                        $parts = [end($parts)];
                    }
                }
                
                // Gabungkan kembali sebagai tokens untuk normalizer
                $tokens = [];
                foreach ($parts as $part) {
                    $tokens = array_merge($tokens, preg_split('/[\s]+/', $part));
                }
                $tokens = array_filter($tokens);
                break;

            case 'kwitansi':
                // Pola: {angka}_{tanggal}_{NAMA LENGKAP}_NENKIN_{tipe}.jpg
                // 1. Hapus token pertama jika berupa angka murni
                if (ctype_digit($tokens[0]) || preg_match('/^\d+$/', $tokens[0])) {
                    array_shift($tokens);
                }
                
                // 2. Hapus token yang memiliki format tanggal (DD-MM-YYYY atau DD-MM-YY)
                $tokens = array_values(array_filter($tokens, function($token) {
                    return !preg_match('/^\d{2}-\d{2}-\d{2,4}$/', $token);
                }));
                
                // 3. Ambil token-token sebelum keyword "NENKIN" (case-insensitive)
                $nenkinIndex = null;
                foreach ($tokens as $index => $token) {
                    if (strcasecmp($token, 'NENKIN') === 0) {
                        $nenkinIndex = $index;
                        break;
                    }
                }
                if ($nenkinIndex !== null) {
                    $tokens = array_slice($tokens, 0, $nenkinIndex);
                }
                break;
        }

        return implode(' ', $tokens);
    }

    /**
     * Match a filename against clients in the database.
     * Returns an array with match information:
     * [
     *     'status' => 'matched' | 'low_confidence' | 'ambiguous' | 'failed',
     *     'confidence' => float,
     *     'matched_client' => Client|null,
     *     'candidates' => [Client, ...],
     *     'best_candidate' => Client|null
     * ]
     */
    public function matchFilename(string $filename, $clients = null, ?string $type = null): array
    {
        $isFallback = false;
        $extractedName = '';

        if ($type !== null) {
            $extractedName = $this->extractNameFromFilename($filename, $type);
        }

        if (empty(trim($extractedName))) {
            $isFallback = true;
            // Fallback: normalize entire filename
            $filenameNormalized = $this->normalize($filename, true);
        } else {
            // Normalize the extracted candidate name
            $filenameNormalized = $this->normalize($extractedName, false);
        }

        // Fetch all active clients or filter by allowed IDs if clients is not passed as pre-loaded collection
        if ($clients === null) {
            $clients = Client::all();
        } elseif (is_array($clients) && !empty($clients) && is_numeric(reset($clients))) {
            // Backward compatibility if an array of IDs is passed:
            $clients = Client::whereIn('id', $clients)->get();
        }

        // 1. Find all exact matches
        $exactMatches = [];
        foreach ($clients as $client) {
            $clientId = $client->id;
            if (!isset($this->normalizedClientsCache[$clientId])) {
                $this->normalizedClientsCache[$clientId] = $this->normalize($client->name, false);
            }
            $clientNameNormalized = $this->normalizedClientsCache[$clientId];

            if ($filenameNormalized === $clientNameNormalized) {
                $exactMatches[] = $client;
            }
        }

        // 2. Ambiguity Detection (Exact match found > 1 client)
        if (count($exactMatches) > 1) {
            return [
                'status' => 'ambiguous',
                'confidence' => 1.000,
                'matched_client' => null,
                'candidates' => $exactMatches,
                'best_candidate' => null,
            ];
        }

        // 3. Exact Match Found == 1 client
        if (count($exactMatches) === 1) {
            $matchedClient = $exactMatches[0];
            $confidence = $isFallback ? 0.850 : 1.000;

            return [
                'status' => $confidence >= 0.95 ? 'matched' : 'low_confidence',
                'confidence' => $confidence,
                'matched_client' => $confidence >= 0.95 ? $matchedClient : null,
                'candidates' => [],
                'best_candidate' => $confidence < 0.95 ? $matchedClient : null,
            ];
        }

        // 4. No Exact Matches -> Perform Fuzzy Matching using similar_text
        $bestMatchClient = null;
        $bestMatchScore = 0.0;

        foreach ($clients as $client) {
            $clientId = $client->id;
            if (!isset($this->normalizedClientsCache[$clientId])) {
                $this->normalizedClientsCache[$clientId] = $this->normalize($client->name, false);
            }
            $clientNameNormalized = $this->normalizedClientsCache[$clientId];

            $lenA = strlen($filenameNormalized);
            $lenB = strlen($clientNameNormalized);
            $maxLen = max($lenA, $lenB);
            $minLen = min($lenA, $lenB);

            if ($maxLen > 0) {
                // Mathematical optimization: if the maximum possible score is less than or equal to the best score found so far, skip similar_text
                $maxPossibleScore = $minLen / $maxLen;
                if ($isFallback) {
                    $maxPossibleScore = min(0.850, $maxPossibleScore);
                }
                if ($maxPossibleScore <= $bestMatchScore) {
                    continue;
                }

                $similarChars = similar_text($filenameNormalized, $clientNameNormalized);
                $score = $similarChars / $maxLen;
            } else {
                $score = 0.0;
            }

            if ($isFallback) {
                $score = min(0.850, $score); // Cap fallback scores to maximum 0.85
            }

            if ($score > $bestMatchScore) {
                $bestMatchScore = $score;
                $bestMatchClient = $client;
            }
        }

        // Round score to 3 decimals
        $bestMatchScore = round($bestMatchScore, 3);

        if ($bestMatchScore >= 0.95) {
            return [
                'status' => 'matched',
                'confidence' => $bestMatchScore,
                'matched_client' => $bestMatchClient,
                'candidates' => [],
                'best_candidate' => null,
            ];
        } elseif ($bestMatchScore >= 0.85) {
            return [
                'status' => 'low_confidence',
                'confidence' => $bestMatchScore,
                'matched_client' => null,
                'candidates' => [],
                'best_candidate' => $bestMatchClient,
            ];
        } else {
            return [
                'status' => 'failed',
                'confidence' => $bestMatchScore,
                'matched_client' => null,
                'candidates' => [],
                'best_candidate' => $bestMatchClient,
            ];
        }
    }
}

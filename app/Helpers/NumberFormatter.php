<?php

namespace App\Helpers;

class NumberFormatter
{
    const DECIMAL_POIN = 2;

    /**
     * Normalize messy phone inputs for direct wa.me redirect.
     * Supports Indonesia (+62) and Japan (+81: 070, 080, 090).
     */
    public static function formatForWhatsApp($phone): string
    {
        if (blank($phone)) {
            return '';
        }

        $phoneStr = trim((string) $phone);

        // TAHAP 1: Simpan Konteks (Niat User) sebelum nomor dirusak
        $hasPlus = str_starts_with($phoneStr, '+');

        // Sanitasi Brutal (Hapus semua kecuali angka)
        $clean = preg_replace('/[^0-9]/', '', $phoneStr);

        if ($clean === '') {
            return '';
        }

        // Deteksi apakah user sengaja mengetik awalan '0' (Format Lokal)
        $isLocal = str_starts_with($clean, '0');

        // TAHAP 2: Pangkas Nol di Depan
        $clean = preg_replace('/^0+/', '', $clean);

        if ($clean === '') {
            return '';
        }

        // TAHAP 3: Perbaikan Salah Ketik "Double Prefix" (+620812 / +81090)
        $clean = preg_replace('/^6262+/', '62', $clean);
        $clean = preg_replace('/^620+/', '62', $clean);
        $clean = preg_replace('/^810+/', '81', $clean);

        // --- TAHAP 4: ROUTING NEGARA (Anti-Clash) ---

        // KONDISI A: User secara eksplisit menggunakan tanda '+'
        // Pasti format internasional, langsung cek kode negaranya.
        if ($hasPlus) {
            if (preg_match('/^62\d{8,13}$/', $clean)) return $clean;
            if (preg_match('/^81[789]0\d{8}$/', $clean)) return $clean;
            // Jika pakai '+' tapi bukan 62/81 (mungkin negara lain), biarkan apa adanya
            return $clean;
        }

        // KONDISI B: User mengetik awalan '0' (Pasti Format Lokal)
        if ($isLocal) {
            // Cek Lokal Jepang (Awalnya 070, 080, 090 - sekarang jadi 70, 80, 90 + 8 digit)
            // Aman dari bentrok karena Indo tidak ada awalan 070/080/090 untuk HP
            if (preg_match('/^[789]0\d{8}$/', $clean)) {
                return '81' . $clean;
            }

            // Cek Lokal Indonesia (Awalnya 08 - sekarang jadi 8 + 7 s.d 12 digit)
            if (preg_match('/^8\d{7,12}$/', $clean)) {
                return '62' . $clean;
            }

            // Fallback untuk lokal selain di atas (misal landline Jakarta 021)
            return '62' . $clean;
        }

        // KONDISI C: Ambiguitas (User tidak mengetik '+' dan tidak mengetik '0')
        // Contoh input: "628123..." atau "819012..." atau "817012..."
        if (preg_match('/^[789]0\d{8}$/', $clean)) {
            return '81' . $clean;
        }
        // Cek format murni Jepang
        if (preg_match('/^81[789]0\d{8}$/', $clean)) {
            return $clean;
        }

        // Cek format murni Indonesia
        if (preg_match('/^62\d{8,13}$/', $clean)) {
            return $clean;
        }

        // Default Fallback
        return '62' . $clean;
    }

    public static function format($number, $decimalPoin = self::DECIMAL_POIN)
    {
        $decimalPoin = fmod($number, 1) !== 0.00 ? $decimalPoin : 0;
        return number_format($number, $decimalPoin, ",", ".");
    }

    public static function imaskToValue($data)
    {

        if (blank($data)) {
            return null;
        }

        $value = trim($data);

        // Indonesian format: 90.000,99
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value); // remove thousand
            $value = str_replace(',', '.', $value); // decimal -> dot
        } else {
            // Only thousand separator
            $value = str_replace('.', '', $value);
        }

        return $value;
    }

    public static function valueToImask($data)
    {
        return str($data)->replace('.', ',')->toString();
    }

    public static function round($data)
    {
        return floor($data * pow(10, self::DECIMAL_POIN)) / pow(10, self::DECIMAL_POIN);
    }

    public static function denominator($nilai)
    {
        $nilai = abs($nilai);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[$nilai];
        } else if ($nilai < 20) {
            $temp = self::denominator($nilai - 10) . " Belas";
        } else if ($nilai < 100) {
            $temp = self::denominator($nilai / 10) . " Puluh" . self::denominator($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . self::denominator($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = self::denominator($nilai / 100) . " Ratus" . self::denominator($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . self::denominator($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = self::denominator($nilai / 1000) . " Ribu" . self::denominator($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = self::denominator($nilai / 1000000) . " Juta" . self::denominator($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = self::denominator($nilai / 1000000000) . " Milyar" . self::denominator(fmod($nilai, 1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = self::denominator($nilai / 1000000000000) . " Trilyun" . self::denominator(fmod($nilai, 1000000000000));
        }
        return $temp;
    }
}

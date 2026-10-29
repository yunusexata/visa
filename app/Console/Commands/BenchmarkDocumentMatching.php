<?php

namespace App\Console\Commands;

use App\Models\Client\Client;
use App\Services\DocumentMatchingService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BenchmarkDocumentMatching extends Command
{
    // Koneksi ini menggunakan service_role tanpa RLS — hanya SELECT read-only, tidak boleh expose data client asli di output manapun.
    protected $signature = 'benchmark:document-matching 
                            {--clients=1000 : Jumlah client yang disimulasikan} 
                            {--files=3000 : Jumlah file yang disimulasikan} 
                            {--confirm-benchmark : Menjalankan benchmark penuh}';

    protected $description = 'Menjalankan benchmark pencocokan nama file dengan similar_text() secara read-only dan aman';

    public function handle()
    {
        $targetClients = (int) $this->option('clients');
        $targetFiles = (int) $this->option('files');
        $confirm = $this->option('confirm-benchmark');

        $this->info('=== Benchmark Document Matching (similar_text) ===');
        $this->info("Simulasi target: {$targetClients} clients x {$targetFiles} files");

        // Validasi input
        if ($targetClients <= 0 || $targetFiles <= 0) {
            $this->error('Error: Jumlah clients dan files harus lebih dari 0.');

            return 1;
        }

        if (! $confirm) {
            $this->warn('STATUS: DRY-RUN (Default)');
            $this->line('Mengapa dry-run? Algoritma similar_text() bersifat CPU-bound O(N*M).');
            $this->line("Menjalankan benchmark dengan {$targetClients} klien dan {$targetFiles} file secara langsung");
            $this->line('dapat menyebabkan CPU contention tinggi pada production server.');
            $this->line('');
            $this->info('Dry-run validasi data:');

            // Cek jumlah client riil di DB secara aman (hanya id dan name)
            // Koneksi ini menggunakan service_role tanpa RLS — hanya SELECT read-only, tidak boleh expose data client asli di output manapun.
            $dbCount = Client::count();
            $this->line("- Jumlah client asli di DB: {$dbCount}");
            $this->line('- Simulasi filenames akan dibuat secara in-memory.');
            $this->line('- Parameter input valid. Gunakan flag --confirm-benchmark untuk eksekusi nyata.');

            return 0;
        }

        $this->warn('PERINGATAN: Menjalankan benchmark nyata pada CPU-bound engine.');
        $this->warn('Pastikan Anda menjalankan ini di luar jam sibuk admin.');

        // 1. Load clients safely (select id dan name saja, no SELECT *)
        // Koneksi ini menggunakan service_role tanpa RLS — hanya SELECT read-only, tidak boleh expose data client asli di output manapun.
        $this->info('Memuat data client...');
        $dbClients = Client::select('id', 'name')->get();
        $dbCount = $dbClients->count();

        if ($dbCount === 0) {
            $this->error('Error: Tidak ada data client di database untuk benchmark.');

            return 1;
        }

        $clients = collect();
        // Duplikasi/pad data klien in-memory jika diperlukan untuk mencapai target jumlah
        while ($clients->count() < $targetClients) {
            foreach ($dbClients as $c) {
                if ($clients->count() >= $targetClients) {
                    break;
                }
                // Pastikan nama klien diduplikasi secara in-memory dengan index unik
                // Ini mencegah modifikasi data asli atau eksposur data asli di log
                $clients->push((object) [
                    'id' => $c->id,
                    'name' => $c->name.' '.($clients->count() + 1),
                ]);
            }
        }

        $this->info("Berhasil menyiapkan {$clients->count()} client in-memory.");

        // 2. Generate 3000 sample filenames in-memory (array PHP)
        $this->info("Membuat {$targetFiles} sample filenames secara in-memory...");
        $filenames = [];
        $types = ['ichijikin', 'resi', 'kwitansi'];
        $banks = ['BCA', 'BNI', 'BRI', 'BTPN', 'MANDI']; // MANDI length 5 to pass resi

        for ($i = 0; $i < $targetFiles; $i++) {
            // Tentukan rasio: 40% exact, 40% fuzzy, 20% unmatched
            $rand = rand(1, 100);
            $type = $types[array_rand($types)];

            // Ambil random client dari list in-memory
            $client = $clients->random();
            $clientName = $client->name;

            if ($rand <= 40) {
                // 40% Exact Match
                $filename = $this->generateFilename($clientName, $type, $banks);
            } elseif ($rand <= 80) {
                // 40% Fuzzy Match (modifikasi nama sedikit)
                $fuzzyName = $this->makeFuzzyName($clientName);
                $filename = $this->generateFilename($fuzzyName, $type, $banks);
            } else {
                // 20% Unmatched
                $randomName = 'Random Name '.Str::random(8);
                $filename = $this->generateFilename($randomName, $type, $banks);
            }

            $filenames[] = [
                'filename' => $filename,
                'type' => $type,
            ];
        }

        // 3. Jalankan Benchmark
        $this->info('Menjalankan pencocokan nama file...');
        $service = new DocumentMatchingService;
        $latencies = [];
        $matchStats = [
            'matched' => 0,
            'low_confidence' => 0,
            'ambiguous' => 0,
            'failed' => 0,
        ];

        $totalStart = microtime(true);
        $bar = $this->output->createProgressBar($targetFiles);
        $bar->start();

        $i = 0;
        foreach ($filenames as $item) {
            $start = microtime(true);

            try {
                // Panggil real service
                $res = $service->matchFilename($item['filename'], $clients, $item['type']);
            } catch (\Throwable $e) {
                // Sembunyikan pesan asli & stack trace demi keamanan data klien
                $this->error("\nError pada iterasi ke-{$i}: ".get_class($e).' (detail disembunyikan untuk keamanan data)');
                $matchStats['failed']++;
                $i++;
                $bar->advance();

                continue;
            }

            $end = microtime(true);
            $durationMs = ($end - $start) * 1000;
            $latencies[] = $durationMs;

            $status = $res['status'] ?? 'failed';
            if (isset($matchStats[$status])) {
                $matchStats[$status]++;
            } else {
                $matchStats['failed']++;
            }

            $i++;
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $totalEnd = microtime(true);
        $totalDuration = $totalEnd - $totalStart;

        // 4. Hitung Latensi Percentile
        sort($latencies);
        $count = count($latencies);
        $p50 = $latencies[(int) ($count * 0.50)];
        $p95 = $latencies[(int) ($count * 0.95)];
        $p99 = $latencies[(int) ($count * 0.99)];
        $avg = array_sum($latencies) / $count;

        // 5. Tampilkan Hasil Anonymized
        $this->line('');
        $this->info('=== HASIL BENCHMARK ===');
        $this->line('Total Durasi: '.number_format($totalDuration, 2).' detik');
        $this->line('Rata-rata: '.number_format($avg, 2).' ms/file');
        $this->line('P50 (Median): '.number_format($p50, 2).' ms');
        $this->line('P95: '.number_format($p95, 2).' ms');
        $this->line('P99: '.number_format($p99, 2).' ms');
        $this->line('');
        $this->info('=== STATISTIK MATCHING (ANONYMIZED) ===');
        $this->line("- Matched: {$matchStats['matched']} files");
        $this->line("- Low Confidence: {$matchStats['low_confidence']} files");
        $this->line("- Ambiguous: {$matchStats['ambiguous']} files");
        $this->line("- Failed: {$matchStats['failed']} files");
        $this->line('');

        // 6. Rekomendasi SYNC_MATCHING_THRESHOLD
        $recommendedThreshold = (int) (10000 / $avg);
        $recommendedThreshold = max(50, min(500, $recommendedThreshold));

        $this->info('=== REKOMENDASI KONFIGURASI ===');
        $this->line('Rata-rata latensi matching per file adalah '.number_format($avg, 2).' ms.');
        $this->line('Untuk menjaga agar respons HTTP preview tetap di bawah 10 detik (sisi server),');
        $this->line('disarankan menggunakan threshold sync/async berikut:');
        $this->info("SYNC_MATCHING_THRESHOLD={$recommendedThreshold}");
        $this->line("(Jika batch berisi > {$recommendedThreshold} file, wajib diproses secara asynchronous di background job).");
        $this->line('');

        // Bersihkan memori secara eksplisit
        unset($clients);
        unset($dbClients);
        unset($filenames);
        unset($latencies);
        gc_collect_cycles();

        return 0;
    }

    private function generateFilename(string $name, string $type, array $banks): string
    {
        $randId = rand(100, 999);
        switch ($type) {
            case 'ichijikin':
                return "{$randId}_{$name}.jpg";
            case 'resi':
                $bank = $banks[array_rand($banks)];

                return "ABC_{$bank}_{$name}_{$randId}.jpg";
            case 'kwitansi':
                return "{$randId}_12-12-2023_{$name}_NENKIN_final.jpg";
            default:
                return "{$randId}_{$name}.jpg";
        }
    }

    private function makeFuzzyName(string $name): string
    {
        $len = strlen($name);
        if ($len <= 5) {
            return $name.'x';
        }
        $pos = rand(0, $len - 2);
        $name[$pos] = 'x';

        return $name;
    }
}

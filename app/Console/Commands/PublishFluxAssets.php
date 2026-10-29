<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishFluxAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flux:publish-assets {--force : Overwrite existing published files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Flux UI assets (JS, CSS, flags) to the public directory for production/Nginx serving';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        $source = base_path('vendor/livewire/flux/dist');
        $destination = public_path('flux');

        if (! $files->isDirectory($source)) {
            $this->error("Flux dist directory not found at [{$source}]. Ensure livewire/flux is installed.");
            return self::FAILURE;
        }

        $files->ensureDirectoryExists($destination);

        // Copy dist directory contents
        $files->copyDirectory($source, $destination);

        // Ensure flux.js exists (alias to flux.min.js if not present)
        $fluxJsPath = $destination . '/flux.js';
        $fluxMinJsPath = $destination . '/flux.min.js';
        if (! $files->exists($fluxJsPath) && $files->exists($fluxMinJsPath)) {
            $files->copy($fluxMinJsPath, $fluxJsPath);
        }

        $this->info('Flux assets published successfully to [' . $destination . '].');

        return self::SUCCESS;
    }
}


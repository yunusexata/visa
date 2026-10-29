<?php

namespace App\Console\Commands;

use Flux\Flux;
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

        // When not Flux Pro, Flux\AssetManager serves flux-lite.min.js for both flux.js and flux.min.js
        $isPro = class_exists(Flux::class) && method_exists(Flux::class, 'pro') && Flux::pro();
        $targetScript = $isPro
            ? base_path('vendor/livewire/flux-pro/dist/flux.min.js')
            : $destination.'/flux-lite.min.js';

        if ($files->exists($targetScript)) {
            $files->copy($targetScript, $destination.'/flux.min.js');
            $files->copy($targetScript, $destination.'/flux.js');
        }

        $this->info('Flux assets published successfully to ['.$destination.'].');

        return self::SUCCESS;
    }
}

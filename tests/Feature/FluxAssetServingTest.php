<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

test('flux assets exist physically in public flux directory', function () {
    expect(File::exists(public_path('flux/flux.min.js')))->toBeTrue();
    expect(File::size(public_path('flux/flux.min.js')))->toBeGreaterThan(1000);

    expect(File::exists(public_path('flux/flux.css')))->toBeTrue();
    expect(File::isDirectory(public_path('flux/flags')))->toBeTrue();
});

test('artisan flux:publish-assets command runs successfully', function () {
    $exitCode = Artisan::call('flux:publish-assets');
    expect($exitCode)->toBe(0);
});

test('app layout uses fluxAppearance and proper script placement', function () {
    $layoutContent = File::get(resource_path('views/layouts/app.blade.php'));

    // Should use @fluxAppearance, not obsolete @fluxStyles
    expect($layoutContent)->toContain('@fluxAppearance');
    expect($layoutContent)->not->toContain('@fluxStyles');

    // Should place @fluxScripts before </body>
    $fluxScriptsPos = strpos($layoutContent, '@fluxScripts');
    $bodyClosePos = strpos($layoutContent, '</body>');

    expect($fluxScriptsPos)->not->toBeFalse();
    expect($bodyClosePos)->not->toBeFalse();
    expect($fluxScriptsPos)->toBeLessThan($bodyClosePos);
});


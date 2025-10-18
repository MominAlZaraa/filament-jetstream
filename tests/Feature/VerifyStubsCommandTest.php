<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->filesystem = new Filesystem;
    $this->stubsPath = app_path('Livewire/FilamentJetstream');
    $this->viewsPath = resource_path('views/livewire/filament-jetstream');

    // Clean up any existing published files
    if ($this->filesystem->exists($this->stubsPath)) {
        $this->filesystem->deleteDirectory($this->stubsPath);
    }
    if ($this->filesystem->exists($this->viewsPath)) {
        $this->filesystem->deleteDirectory($this->viewsPath);
    }
});

afterEach(function () {
    // Clean up after tests
    if ($this->filesystem->exists($this->stubsPath)) {
        $this->filesystem->deleteDirectory($this->stubsPath);
    }
    if ($this->filesystem->exists($this->viewsPath)) {
        $this->filesystem->deleteDirectory($this->viewsPath);
    }
});

test('verify-stubs command runs successfully', function () {
    $exitCode = Artisan::call('filament-jetstream:verify-stubs');

    expect($exitCode)->toBe(0);
});

test('verify-stubs command shows no published components when none exist', function () {
    Artisan::call('filament-jetstream:verify-stubs');

    $output = Artisan::output();

    expect($output)->toContain('Filament Jetstream Component Discovery Status')
        ->and($output)->toContain('Configuration:')
        ->and($output)->toContain('Components:')
        ->and($output)->toContain('Views:');
});

test('verify-stubs command detects published components', function () {
    // Publish some stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    Artisan::call('filament-jetstream:verify-stubs');

    $output = Artisan::output();

    expect($output)->toContain('profile.update_profile_information')
        ->and($output)->toContain('Using published component');
});

test('verify-stubs command detects published views', function () {
    // Publish stubs including views
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    Artisan::call('filament-jetstream:verify-stubs');

    $output = Artisan::output();

    expect($output)->toContain('Views:')
        ->and($output)->toContain('Using published view');
});

test('verify-stubs command supports json output', function () {
    Artisan::call('filament-jetstream:verify-stubs', ['--json' => true]);

    $output = Artisan::output();

    $json = json_decode($output, true);

    expect($json)->toBeArray()
        ->and($json)->toHaveKeys(['config', 'components', 'views']);
});

test('verify-stubs command shows config override status', function () {
    config(['filament-jetstream.components.profile.update_profile_information' => 'App\Custom\UpdateProfile']);

    Artisan::call('filament-jetstream:verify-stubs');

    $output = Artisan::output();

    expect($output)->toContain('Using config override');
});

test('verify-stubs command shows summary when no stubs published', function () {
    Artisan::call('filament-jetstream:verify-stubs');

    $output = Artisan::output();

    expect($output)->toContain('No published components or views detected');
});

test('verify-stubs command shows summary when stubs are published', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    Artisan::call('filament-jetstream:verify-stubs');

    $output = Artisan::output();

    expect($output)->toContain('Published components and views are being used!');
});

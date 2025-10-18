<?php

use Filament\Jetstream\ComponentResolver;
use Filament\Jetstream\Livewire\Profile\UpdateProfileInformation as PackageUpdateProfileInformation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

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

    // Clear ComponentResolver cache
    ComponentResolver::clearCache();
});

afterEach(function () {
    // Clean up after tests
    if ($this->filesystem->exists($this->stubsPath)) {
        $this->filesystem->deleteDirectory($this->stubsPath);
    }
    if ($this->filesystem->exists($this->viewsPath)) {
        $this->filesystem->deleteDirectory($this->viewsPath);
    }

    // Clear ComponentResolver cache
    ComponentResolver::clearCache();
});

test('ComponentResolver returns default when no stubs are published', function () {
    Config::set('filament-jetstream.auto_discover', true);

    $result = ComponentResolver::resolveComponent('profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe(PackageUpdateProfileInformation::class);
});

test('ComponentResolver discovers published components when files exist', function () {
    Config::set('filament-jetstream.auto_discover', true);

    // Publish the stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    // Clear cache to force re-discovery
    ComponentResolver::clearCache();

    $result = ComponentResolver::resolveComponent('profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe('App\Livewire\FilamentJetstream\Profile\UpdateProfileInformation');
});

test('ComponentResolver respects auto_discover setting', function () {
    Config::set('filament-jetstream.auto_discover', false);

    // Publish the stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    // Clear cache to force re-discovery
    ComponentResolver::clearCache();

    $result = ComponentResolver::resolveComponent('profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe(PackageUpdateProfileInformation::class);
});

test('ComponentResolver returns config override when set', function () {
    Config::set('filament-jetstream.components.profile.update_profile_information', 'App\Custom\UpdateProfile');

    $result = ComponentResolver::resolveComponent('profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe('App\Custom\UpdateProfile');
});

test('ComponentResolver caches discovery results', function () {
    Config::set('filament-jetstream.auto_discover', true);

    // Publish the stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    // Clear cache to force re-discovery
    ComponentResolver::clearCache();

    // First call should discover
    $result1 = ComponentResolver::discoverPublishedComponent('profile.update_profile_information');

    // Delete the file
    $filePath = app_path('Livewire/FilamentJetstream/Profile/UpdateProfileInformation.php');
    $this->filesystem->delete($filePath);

    // Second call should return cached result (file was deleted but cache should still work)
    $result2 = ComponentResolver::discoverPublishedComponent('profile.update_profile_information');

    expect($result1)->toBe('App\Livewire\FilamentJetstream\Profile\UpdateProfileInformation')
        ->and($result2)->toBe('App\Livewire\FilamentJetstream\Profile\UpdateProfileInformation');
});

test('ComponentResolver discovers published views', function () {
    // Create a published view
    $viewPath = resource_path('views/livewire/filament-jetstream/profile/update-profile-information.blade.php');
    $this->filesystem->ensureDirectoryExists(dirname($viewPath));
    $this->filesystem->put($viewPath, '<div>Custom View</div>');

    // Clear cache to force re-discovery
    ComponentResolver::clearCache();

    $result = ComponentResolver::discoverPublishedView('profile.update_profile_information');

    expect($result)->toBe('livewire.filament-jetstream.profile.update-profile-information');
});

test('ComponentResolver returns null for non-published views', function () {
    $result = ComponentResolver::discoverPublishedView('profile.update_profile_information');

    expect($result)->toBeNull();
});

test('ComponentResolver provides diagnostic information', function () {
    Config::set('filament-jetstream.auto_discover', true);

    // Publish some stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    // Clear cache to force re-discovery
    ComponentResolver::clearCache();

    $diagnostics = ComponentResolver::getDiagnostics();

    expect($diagnostics)->toBeArray()
        ->and($diagnostics)->toHaveKeys(['config', 'components', 'views'])
        ->and($diagnostics['config'])->toHaveKey('auto_discover')
        ->and($diagnostics['components'])->toBeArray()
        ->and($diagnostics['views'])->toBeArray();
});

test('ComponentResolver handles invalid component keys', function () {
    $result = ComponentResolver::discoverPublishedComponent('invalid.component.key');

    expect($result)->toBeNull();
});

test('ComponentResolver checks file existence before class_exists', function () {
    Config::set('filament-jetstream.auto_discover', true);

    // Don't publish anything
    $result = ComponentResolver::discoverPublishedComponent('profile.update_profile_information');

    // Should return null because file doesn't exist
    expect($result)->toBeNull();
});

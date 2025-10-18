<?php

use Filament\Jetstream\Livewire\Profile\UpdateProfileInformation as PackageUpdateProfileInformation;
use Filament\Jetstream\Livewire\Profile\UpdatePassword as PackageUpdatePassword;
use Filament\Jetstream\Livewire\Teams\UpdateTeamName as PackageUpdateTeamName;
use Filament\Jetstream\Livewire\ApiTokens\CreateApiToken as PackageCreateApiToken;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->filesystem = new Filesystem;
    $this->stubsPath = app_path('Livewire/FilamentJetstream');

    // Clean up any existing published files
    if ($this->filesystem->exists($this->stubsPath)) {
        $this->filesystem->deleteDirectory($this->stubsPath);
    }
});

afterEach(function () {
    // Clean up after tests
    if ($this->filesystem->exists($this->stubsPath)) {
        $this->filesystem->deleteDirectory($this->stubsPath);
    }
});

test('package uses default components when no stubs are published', function () {
    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected resolveComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('resolveComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe(PackageUpdateProfileInformation::class);
});

test('package discovers published components when auto_discover is enabled', function () {
    Config::set('filament-jetstream.auto_discover', true);

    // Publish the stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile']);

    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected resolveComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('resolveComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe('App\Livewire\FilamentJetstream\Profile\UpdateProfileInformation');
});

test('package uses default components when auto_discover is disabled', function () {
    Config::set('filament-jetstream.auto_discover', false);

    // Publish the stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile']);

    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected resolveComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('resolveComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe(PackageUpdateProfileInformation::class);
});

test('package uses config-specified component override', function () {
    Config::set('filament-jetstream.components.profile.update_profile_information', 'App\Custom\UpdateProfile');

    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected resolveComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('resolveComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe('App\Custom\UpdateProfile');
});

test('config override takes precedence over auto_discover', function () {
    Config::set('filament-jetstream.auto_discover', true);
    Config::set('filament-jetstream.components.profile.update_profile_information', 'App\Custom\UpdateProfile');

    // Publish the stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile']);

    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected resolveComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('resolveComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'profile.update_profile_information', PackageUpdateProfileInformation::class);

    expect($result)->toBe('App\Custom\UpdateProfile');
});

test('auto_discover works for team components', function () {
    Config::set('filament-jetstream.auto_discover', true);

    // Publish the stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'teams']);

    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected resolveComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('resolveComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'teams.update_team_name', PackageUpdateTeamName::class);

    expect($result)->toBe('App\Livewire\FilamentJetstream\Teams\UpdateTeamName');
});

test('auto_discover works for api token components', function () {
    Config::set('filament-jetstream.auto_discover', true);

    // Publish the stubs
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'api']);

    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected resolveComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('resolveComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'api_tokens.create_api_token', PackageCreateApiToken::class);

    expect($result)->toBe('App\Livewire\FilamentJetstream\ApiTokens\CreateApiToken');
});

test('discover published component returns null for non-existent class', function () {
    Config::set('filament-jetstream.auto_discover', true);

    // Don't publish stubs

    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected discoverPublishedComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('discoverPublishedComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'profile.update_profile_information');

    expect($result)->toBeNull();
});

test('discover published component returns null for invalid key', function () {
    // Get the service provider instance
    $provider = app(\Filament\Jetstream\JetstreamServiceProvider::class);
    
    // Use reflection to test the protected discoverPublishedComponent method
    $reflection = new \ReflectionClass($provider);
    $method = $reflection->getMethod('discoverPublishedComponent');
    $method->setAccessible(true);

    $result = $method->invoke($provider, 'invalid.component.key');

    expect($result)->toBeNull();
});

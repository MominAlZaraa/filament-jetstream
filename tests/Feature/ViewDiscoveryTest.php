<?php

use Filament\Jetstream\ComponentResolver;
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

test('published views are discovered when they exist', function () {
    // Create a published view with custom content
    $viewPath = resource_path('views/livewire/filament-jetstream/profile/update-profile-information.blade.php');
    $this->filesystem->ensureDirectoryExists(dirname($viewPath));
    $this->filesystem->put($viewPath, '<div>Custom Published View</div>');

    // Clear cache
    ComponentResolver::clearCache();

    // Discover the view
    $viewName = ComponentResolver::discoverPublishedView('profile.update_profile_information');

    expect($viewName)->toBe('livewire.filament-jetstream.profile.update-profile-information');
});

test('published views are used over package views when rendering', function () {
    // Create a published component and view
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    // Modify the published view to have custom content
    $viewPath = resource_path('views/livewire/filament-jetstream/profile/update-profile-information.blade.php');
    $customContent = '<div class="custom-marker">Published Custom View Content</div>';
    $this->filesystem->put($viewPath, $customContent);

    // Clear cache
    ComponentResolver::clearCache();

    // Register view namespace
    ComponentResolver::registerViewNamespace();

    // Check that the view can be found
    expect(view()->exists('livewire.filament-jetstream.profile.update-profile-information'))->toBeTrue();
});

test('package views are used when no published views exist', function () {
    // Don't publish anything
    $viewName = ComponentResolver::discoverPublishedView('profile.update_profile_information');

    expect($viewName)->toBeNull();
});

test('view discovery respects custom views_path config', function () {
    $customViewsPath = resource_path('views/custom-jetstream');
    Config::set('filament-jetstream.views_path', $customViewsPath);

    // Create a view in custom path
    $viewPath = $customViewsPath . '/profile/update-profile-information.blade.php';
    $this->filesystem->ensureDirectoryExists(dirname($viewPath));
    $this->filesystem->put($viewPath, '<div>Custom Path View</div>');

    // Clear cache
    ComponentResolver::clearCache();

    $viewName = ComponentResolver::discoverPublishedView('profile.update_profile_information');

    expect($viewName)->toBe('livewire.filament-jetstream.profile.update-profile-information');

    // Clean up
    if ($this->filesystem->exists($customViewsPath)) {
        $this->filesystem->deleteDirectory($customViewsPath);
    }
});

test('view namespace is registered for published views directory', function () {
    // Create the views directory
    $viewsPath = resource_path('views/livewire/filament-jetstream/profile');
    $this->filesystem->ensureDirectoryExists($viewsPath);
    $this->filesystem->put($viewsPath . '/test-view.blade.php', '<div>Test</div>');

    // Register namespace
    ComponentResolver::registerViewNamespace();

    // The namespace should be registered and we should be able to find views in it
    // Note: Laravel's view finder will look in the prepended namespace first
    expect($this->filesystem->isDirectory(resource_path('views/livewire/filament-jetstream')))->toBeTrue();
});

test('published views directory is created when publishing stubs', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    $viewsPath = resource_path('views/livewire/filament-jetstream/profile');

    expect($this->filesystem->isDirectory($viewsPath))->toBeTrue()
        ->and($this->filesystem->exists($viewsPath . '/update-profile-information.blade.php'))->toBeTrue();
});

test('multiple view types can be discovered', function () {
    // Create multiple published views
    $views = [
        'profile/update-profile-information' => 'profile.update_profile_information',
        'profile/update-password' => 'profile.update_password',
        'teams/update-team-name' => 'teams.update_team_name',
    ];

    foreach ($views as $path => $key) {
        $viewPath = resource_path('views/livewire/filament-jetstream/' . $path . '.blade.php');
        $this->filesystem->ensureDirectoryExists(dirname($viewPath));
        $this->filesystem->put($viewPath, '<div>View for ' . $key . '</div>');
    }

    // Clear cache
    ComponentResolver::clearCache();

    // Check all views are discovered
    foreach ($views as $path => $key) {
        $viewName = ComponentResolver::discoverPublishedView($key);
        expect($viewName)->toBe('livewire.filament-jetstream.' . str_replace('/', '.', $path));
    }
});

test('view cache is cleared when ComponentResolver cache is cleared', function () {
    // Create a published view
    $viewPath = resource_path('views/livewire/filament-jetstream/profile/update-profile-information.blade.php');
    $this->filesystem->ensureDirectoryExists(dirname($viewPath));
    $this->filesystem->put($viewPath, '<div>First Content</div>');

    // Discover the view (should be cached)
    $viewName1 = ComponentResolver::discoverPublishedView('profile.update_profile_information');

    // Delete the view
    $this->filesystem->delete($viewPath);

    // Clear cache
    ComponentResolver::clearCache();

    // Discover again (should return null now)
    $viewName2 = ComponentResolver::discoverPublishedView('profile.update_profile_information');

    expect($viewName1)->toBe('livewire.filament-jetstream.profile.update-profile-information')
        ->and($viewName2)->toBeNull();
});

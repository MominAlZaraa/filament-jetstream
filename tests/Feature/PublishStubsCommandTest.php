<?php

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

test('publish stubs command publishes profile components', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    expect($this->filesystem->exists($this->stubsPath . '/Profile/UpdateProfileInformation.php'))->toBeTrue()
        ->and($this->filesystem->exists($this->stubsPath . '/Profile/UpdatePassword.php'))->toBeTrue()
        ->and($this->filesystem->exists($this->stubsPath . '/Profile/DeleteAccount.php'))->toBeTrue()
        ->and($this->filesystem->exists($this->stubsPath . '/Profile/LogoutOtherBrowserSessions.php'))->toBeTrue();
});

test('publish stubs command publishes team components', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'teams', '--skip-cache-clear' => true]);

    expect($this->filesystem->exists($this->stubsPath . '/Teams/UpdateTeamName.php'))->toBeTrue()
        ->and($this->filesystem->exists($this->stubsPath . '/Teams/AddTeamMember.php'))->toBeTrue()
        ->and($this->filesystem->exists($this->stubsPath . '/Teams/TeamMembers.php'))->toBeTrue()
        ->and($this->filesystem->exists($this->stubsPath . '/Teams/PendingTeamInvitations.php'))->toBeTrue()
        ->and($this->filesystem->exists($this->stubsPath . '/Teams/DeleteTeam.php'))->toBeTrue();
});

test('publish stubs command publishes api components', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'api', '--skip-cache-clear' => true]);

    expect($this->filesystem->exists($this->stubsPath . '/ApiTokens/CreateApiToken.php'))->toBeTrue()
        ->and($this->filesystem->exists($this->stubsPath . '/ApiTokens/ManageApiTokens.php'))->toBeTrue();
});

test('publish stubs command publishes all components without only option', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--skip-cache-clear' => true]);

    // Profile components
    expect($this->filesystem->exists($this->stubsPath . '/Profile/UpdateProfileInformation.php'))->toBeTrue();

    // Team components
    expect($this->filesystem->exists($this->stubsPath . '/Teams/UpdateTeamName.php'))->toBeTrue();

    // API components
    expect($this->filesystem->exists($this->stubsPath . '/ApiTokens/CreateApiToken.php'))->toBeTrue();
});

test('publish stubs command does not overwrite existing files without force option', function () {
    $testFile = $this->stubsPath . '/Profile/UpdateProfileInformation.php';

    // Create directory and file
    $this->filesystem->ensureDirectoryExists(dirname($testFile));
    $this->filesystem->put($testFile, '<?php // Original content');

    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    $content = $this->filesystem->get($testFile);

    expect($content)->toBe('<?php // Original content');
});

test('publish stubs command overwrites existing files with force option', function () {
    $testFile = $this->stubsPath . '/Profile/UpdateProfileInformation.php';

    // Create directory and file
    $this->filesystem->ensureDirectoryExists(dirname($testFile));
    $this->filesystem->put($testFile, '<?php // Original content');

    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--force' => true, '--skip-cache-clear' => true]);

    $content = $this->filesystem->get($testFile);

    expect($content)->not->toBe('<?php // Original content')
        ->and($content)->toContain('namespace App\Livewire\FilamentJetstream\Profile');
});

test('publish stubs command publishes views', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    // Check if at least one view was published (views are copied from package)
    expect($this->filesystem->exists($this->viewsPath . '/profile'))->toBeTrue();
});

test('publish stubs command respects custom config paths', function () {
    $customStubsPath = app_path('CustomLivewire');
    $customViewsPath = resource_path('views/custom');

    Config::set('filament-jetstream.stubs_path', $customStubsPath);
    Config::set('filament-jetstream.views_path', $customViewsPath);

    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    expect($this->filesystem->exists($customStubsPath . '/Profile/UpdateProfileInformation.php'))->toBeTrue();

    // Clean up custom paths
    if ($this->filesystem->exists($customStubsPath)) {
        $this->filesystem->deleteDirectory($customStubsPath);
    }
    if ($this->filesystem->exists($customViewsPath)) {
        $this->filesystem->deleteDirectory($customViewsPath);
    }
});

test('publish stubs command returns success when files are published', function () {
    $exitCode = Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    expect($exitCode)->toBe(0);
});

test('published component files have correct namespace', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    $content = $this->filesystem->get($this->stubsPath . '/Profile/UpdateProfileInformation.php');

    expect($content)->toContain('namespace App\Livewire\FilamentJetstream\Profile');
});

test('published component files have correct view paths', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    $content = $this->filesystem->get($this->stubsPath . '/Profile/UpdateProfileInformation.php');

    expect($content)->toContain('livewire.filament-jetstream.profile.update-profile-information');
});

test('publish stubs command clears caches by default', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile']);

    $output = Artisan::output();

    expect($output)->toContain('Clearing caches');
});

test('publish stubs command skips cache clearing when option is set', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    $output = Artisan::output();

    expect($output)->not->toContain('Clearing caches');
});

test('publish stubs command recommends verify command', function () {
    Artisan::call('filament-jetstream:publish-stubs', ['--only' => 'profile', '--skip-cache-clear' => true]);

    $output = Artisan::output();

    expect($output)->toContain('php artisan filament-jetstream:verify-stubs');
});

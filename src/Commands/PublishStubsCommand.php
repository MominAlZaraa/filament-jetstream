<?php

namespace Filament\Jetstream\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class PublishStubsCommand extends Command
{
    public $signature = 'filament-jetstream:publish-stubs
                        {--force : Overwrite existing files}
                        {--only= : Only publish specific groups (profile,teams,api)}';

    public $description = 'Publish Filament Jetstream Livewire component stubs for customization';

    protected Filesystem $files;

    protected array $publishedFiles = [];

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(): int
    {
        $this->info('Publishing Filament Jetstream stubs...');

        $only = $this->option('only');
        $groups = $only ? explode(',', $only) : ['profile', 'teams', 'api'];

        foreach ($groups as $group) {
            $group = trim($group);

            match ($group) {
                'profile' => $this->publishProfileStubs(),
                'teams' => $this->publishTeamStubs(),
                'api' => $this->publishApiStubs(),
                default => $this->error("Unknown group: {$group}"),
            };
        }

        if (empty($this->publishedFiles)) {
            $this->warn('No files were published.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Published files:');

        foreach ($this->publishedFiles as $file) {
            $this->line("  - {$file}");
        }

        $this->newLine();
        $this->info('Stubs published successfully!');
        $this->comment('You can now customize the published files in your application.');

        return self::SUCCESS;
    }

    protected function publishProfileStubs(): void
    {
        $this->comment('Publishing profile stubs...');

        $this->publishStub(
            'Profile/UpdateProfileInformation',
            'Profile/UpdateProfileInformation.php'
        );

        $this->publishStub(
            'Profile/UpdatePassword',
            'Profile/UpdatePassword.php'
        );

        $this->publishStub(
            'Profile/DeleteAccount',
            'Profile/DeleteAccount.php'
        );

        $this->publishStub(
            'Profile/LogoutOtherBrowserSessions',
            'Profile/LogoutOtherBrowserSessions.php'
        );

        // Publish views
        $this->publishView(
            'profile/update-profile-information',
            'profile/update-profile-information.blade.php'
        );

        $this->publishView(
            'profile/update-password',
            'profile/update-password.blade.php'
        );

        $this->publishView(
            'profile/delete-account',
            'profile/delete-account.blade.php'
        );

        $this->publishView(
            'profile/logout-other-browser-sessions',
            'profile/logout-other-browser-sessions.blade.php'
        );
    }

    protected function publishTeamStubs(): void
    {
        $this->comment('Publishing team stubs...');

        $this->publishStub(
            'Teams/UpdateTeamName',
            'Teams/UpdateTeamName.php'
        );

        $this->publishStub(
            'Teams/AddTeamMember',
            'Teams/AddTeamMember.php'
        );

        $this->publishStub(
            'Teams/TeamMembers',
            'Teams/TeamMembers.php'
        );

        $this->publishStub(
            'Teams/PendingTeamInvitations',
            'Teams/PendingTeamInvitations.php'
        );

        $this->publishStub(
            'Teams/DeleteTeam',
            'Teams/DeleteTeam.php'
        );

        // Publish views
        $this->publishView(
            'teams/update-team-name',
            'teams/update-team-name.blade.php'
        );

        $this->publishView(
            'teams/add-team-member',
            'teams/add-team-member.blade.php'
        );

        $this->publishView(
            'teams/team-members',
            'teams/team-members.blade.php'
        );

        $this->publishView(
            'teams/pending-team-invitations',
            'teams/pending-team-invitations.blade.php'
        );

        $this->publishView(
            'teams/delete-team',
            'teams/delete-team.blade.php'
        );
    }

    protected function publishApiStubs(): void
    {
        $this->comment('Publishing API token stubs...');

        $this->publishStub(
            'ApiTokens/CreateApiToken',
            'ApiTokens/CreateApiToken.php'
        );

        $this->publishStub(
            'ApiTokens/ManageApiTokens',
            'ApiTokens/ManageApiTokens.php'
        );

        // Publish views
        $this->publishView(
            'api-tokens/create-api-token',
            'api-tokens/create-api-token.blade.php'
        );

        $this->publishView(
            'api-tokens/manage-api-tokens',
            'api-tokens/manage-api-tokens.blade.php'
        );
    }

    protected function publishStub(string $stub, string $destination): void
    {
        $stubPath = $this->getStubPath($stub);
        $destinationPath = $this->getDestinationPath($destination);

        if (! $this->files->exists($stubPath)) {
            $this->warn("Stub not found: {$stub}");

            return;
        }

        if ($this->files->exists($destinationPath) && ! $this->option('force')) {
            $this->warn("File already exists: {$destination}");

            return;
        }

        $this->files->ensureDirectoryExists(dirname($destinationPath));

        $this->files->copy($stubPath, $destinationPath);

        $this->publishedFiles[] = $destination;
    }

    protected function publishView(string $view, string $destination): void
    {
        $viewPath = $this->getViewStubPath($view);
        $destinationPath = $this->getViewDestinationPath($destination);

        if (! $this->files->exists($viewPath)) {
            // If stub doesn't exist, copy from package views
            $packageViewPath = $this->getPackageViewPath($view);

            if (! $this->files->exists($packageViewPath)) {
                $this->warn("View not found: {$view}");

                return;
            }

            $viewPath = $packageViewPath;
        }

        if ($this->files->exists($destinationPath) && ! $this->option('force')) {
            $this->warn("View already exists: {$destination}");

            return;
        }

        $this->files->ensureDirectoryExists(dirname($destinationPath));

        $this->files->copy($viewPath, $destinationPath);

        $this->publishedFiles[] = "views/{$destination}";
    }

    protected function getStubPath(string $stub): string
    {
        return __DIR__ . "/../../stubs/livewire/{$stub}.php.stub";
    }

    protected function getViewStubPath(string $view): string
    {
        return __DIR__ . "/../../stubs/views/{$view}.blade.php.stub";
    }

    protected function getPackageViewPath(string $view): string
    {
        return __DIR__ . "/../../resources/views/livewire/{$view}.blade.php";
    }

    protected function getDestinationPath(string $destination): string
    {
        $basePath = config('filament-jetstream.stubs_path', app_path('Livewire/FilamentJetstream'));

        return $basePath . '/' . $destination;
    }

    protected function getViewDestinationPath(string $destination): string
    {
        $basePath = config('filament-jetstream.views_path', resource_path('views/livewire/filament-jetstream'));

        return $basePath . '/' . $destination;
    }
}

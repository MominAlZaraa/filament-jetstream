<?php

namespace Filament\Jetstream;

use Filament\Jetstream\Commands\InstallCommand;
use Filament\Jetstream\Commands\PublishStubsCommand;
use Filament\Jetstream\Commands\VerifyStubsCommand;
use Filament\Jetstream\Livewire\ApiTokens\CreateApiToken;
use Filament\Jetstream\Livewire\ApiTokens\ManageApiTokens;
use Filament\Jetstream\Livewire\Profile\DeleteAccount;
use Filament\Jetstream\Livewire\Profile\LogoutOtherBrowserSessions;
use Filament\Jetstream\Livewire\Profile\UpdatePassword;
use Filament\Jetstream\Livewire\Profile\UpdateProfileInformation;
use Filament\Jetstream\Livewire\Teams\AddTeamMember;
use Filament\Jetstream\Livewire\Teams\DeleteTeam;
use Filament\Jetstream\Livewire\Teams\PendingTeamInvitations;
use Filament\Jetstream\Livewire\Teams\TeamMembers;
use Filament\Jetstream\Livewire\Teams\UpdateTeamName;
use Filament\Jetstream\Pages\ApiTokens;
use Filament\Jetstream\Pages\EditProfile;
use Filament\Jetstream\Pages\EditTeam;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class JetstreamServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-jetstream';

    public static string $viewNamespace = 'filament-jetstream';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasViews()
            ->hasTranslations()
            ->hasConfigFile(static::$name)
            ->hasCommands([
                InstallCommand::class,
                PublishStubsCommand::class,
                VerifyStubsCommand::class,
            ]);

        $this->publishes([
            __DIR__ . '/../database/migrations/2025_08_22_134103_add_profile_photo_column_to_users_table.php' => database_path('migrations/2025_08_22_134103_add_profile_photo_column_to_users_table.php'),
        ], 'filament-jetstream-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/2025_08_22_134103_create_teams_table.php' => database_path('migrations/2025_08_22_134103_create_teams_table.php'),
        ], 'filament-jetstream-team-migrations');
    }

    public function packageBooted()
    {
        $this->registerLivewireComponents();
        ComponentResolver::registerViewNamespace();
    }

    private function registerLivewireComponents(): void
    {
        /*
         * Profile Components
         */
        Livewire::component('filament-jetstream::pages.edit-profile', EditProfile::class);
        Livewire::component(
            'filament-jetstream::livewire.profile.update-profile-information',
            $this->resolveComponent('profile.update_profile_information', UpdateProfileInformation::class)
        );
        Livewire::component(
            'filament-jetstream::livewire.profile.update-password',
            $this->resolveComponent('profile.update_password', UpdatePassword::class)
        );
        Livewire::component(
            'filament-jetstream::livewire.profile.logout-other-browser-sessions',
            $this->resolveComponent('profile.logout_other_browser_sessions', LogoutOtherBrowserSessions::class)
        );
        Livewire::component(
            'filament-jetstream::livewire.profile.delete-account',
            $this->resolveComponent('profile.delete_account', DeleteAccount::class)
        );

        /*
         * Api Token Components
         */
        Livewire::component('filament-jetstream::pages.api-tokens', ApiTokens::class);
        Livewire::component(
            'filament-jetstream::livewire.api-tokens.create-api-token',
            $this->resolveComponent('api_tokens.create_api_token', CreateApiToken::class)
        );
        Livewire::component(
            'filament-jetstream::livewire.api-tokens.manage-api-tokens',
            $this->resolveComponent('api_tokens.manage_api_tokens', ManageApiTokens::class)
        );

        /*
         * Teams Components
         */
        Livewire::component('filament-jetstream::pages.edit-teams', EditTeam::class);
        Livewire::component(
            'filament-jetstream::livewire.teams.update-team-name',
            $this->resolveComponent('teams.update_team_name', UpdateTeamName::class)
        );
        Livewire::component(
            'filament-jetstream::livewire.teams.add-team-member',
            $this->resolveComponent('teams.add_team_member', AddTeamMember::class)
        );
        Livewire::component(
            'filament-jetstream::livewire.teams.team-members',
            $this->resolveComponent('teams.team_members', TeamMembers::class)
        );
        Livewire::component(
            'filament-jetstream::livewire.teams.pending-team-invitations',
            $this->resolveComponent('teams.pending_team_invitations', PendingTeamInvitations::class)
        );
        Livewire::component(
            'filament-jetstream::livewire.teams.delete-team',
            $this->resolveComponent('teams.delete_team', DeleteTeam::class)
        );
    }

    /**
     * Resolve the component class to use, preferring published components if auto-discovery is enabled.
     */
    private function resolveComponent(string $key, string $default): string
    {
        return ComponentResolver::resolveComponent($key, $default);
    }
}

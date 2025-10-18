<?php

namespace Filament\Jetstream;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class ComponentResolver
{
    protected static array $componentCache = [];

    protected static array $viewCache = [];

    /**
     * Component mapping from config keys to class paths
     */
    protected static array $componentMap = [
        'profile.update_profile_information' => 'Profile\\UpdateProfileInformation',
        'profile.update_password' => 'Profile\\UpdatePassword',
        'profile.delete_account' => 'Profile\\DeleteAccount',
        'profile.logout_other_browser_sessions' => 'Profile\\LogoutOtherBrowserSessions',
        'teams.update_team_name' => 'Teams\\UpdateTeamName',
        'teams.add_team_member' => 'Teams\\AddTeamMember',
        'teams.team_members' => 'Teams\\TeamMembers',
        'teams.pending_team_invitations' => 'Teams\\PendingTeamInvitations',
        'teams.delete_team' => 'Teams\\DeleteTeam',
        'api_tokens.create_api_token' => 'ApiTokens\\CreateApiToken',
        'api_tokens.manage_api_tokens' => 'ApiTokens\\ManageApiTokens',
    ];

    /**
     * View mapping from config keys to view paths
     */
    protected static array $viewMap = [
        'profile.update_profile_information' => 'profile/update-profile-information',
        'profile.update_password' => 'profile/update-password',
        'profile.delete_account' => 'profile/delete-account',
        'profile.logout_other_browser_sessions' => 'profile/logout-other-browser-sessions',
        'teams.update_team_name' => 'teams/update-team-name',
        'teams.add_team_member' => 'teams/add-team-member',
        'teams.team_members' => 'teams/team-members',
        'teams.pending_team_invitations' => 'teams/pending-team-invitations',
        'teams.delete_team' => 'teams/delete-team',
        'api_tokens.create_api_token' => 'api-tokens/create-api-token',
        'api_tokens.manage_api_tokens' => 'api-tokens/manage-api-tokens',
    ];

    /**
     * Resolve the component class to use, preferring published components if auto-discovery is enabled.
     */
    public static function resolveComponent(string $key, string $default): string
    {
        // Check if a custom component is specified in config
        $customComponent = config("filament-jetstream.components.{$key}");

        if ($customComponent !== null) {
            return $customComponent;
        }

        // If auto-discovery is disabled, use the default
        if (! config('filament-jetstream.auto_discover', true)) {
            return $default;
        }

        // Try to discover published component
        $discoveredComponent = static::discoverPublishedComponent($key);

        return $discoveredComponent ?? $default;
    }

    /**
     * Discover published component in the application.
     */
    public static function discoverPublishedComponent(string $key): ?string
    {
        // Check cache first
        if (isset(static::$componentCache[$key])) {
            return static::$componentCache[$key];
        }

        if (! isset(static::$componentMap[$key])) {
            static::$componentCache[$key] = null;

            return null;
        }

        $classPath = static::$componentMap[$key];
        $className = 'App\\Livewire\\FilamentJetstream\\' . $classPath;

        // Check if the file exists first (more reliable than class_exists during boot)
        $filePath = app_path('Livewire/FilamentJetstream/' . str_replace('\\', '/', $classPath) . '.php');

        if (! File::exists($filePath)) {
            static::$componentCache[$key] = null;

            return null;
        }

        // Now verify the class exists (trigger autoload)
        if (! class_exists($className)) {
            static::$componentCache[$key] = null;

            return null;
        }

        // Cache the result
        static::$componentCache[$key] = $className;

        return $className;
    }

    /**
     * Discover published view for a component.
     */
    public static function discoverPublishedView(string $key): ?string
    {
        // Check cache first
        if (isset(static::$viewCache[$key])) {
            return static::$viewCache[$key];
        }

        if (! isset(static::$viewMap[$key])) {
            static::$viewCache[$key] = null;

            return null;
        }

        $viewPath = static::$viewMap[$key];
        $viewsBasePath = config('filament-jetstream.views_path', resource_path('views/livewire/filament-jetstream'));

        // Check for both .blade.php extensions
        $filePath = $viewsBasePath . '/' . $viewPath . '.blade.php';

        if (File::exists($filePath)) {
            // Cache and return the view name
            $viewName = 'livewire.filament-jetstream.' . str_replace('/', '.', $viewPath);
            static::$viewCache[$key] = $viewName;

            return $viewName;
        }

        static::$viewCache[$key] = null;

        return null;
    }

    /**
     * Register view namespace for published views.
     */
    public static function registerViewNamespace(): void
    {
        $viewsPath = config('filament-jetstream.views_path', resource_path('views/livewire/filament-jetstream'));

        if (File::isDirectory($viewsPath)) {
            // Add published views location with higher priority
            View::prependNamespace('livewire.filament-jetstream', $viewsPath);
        }
    }

    /**
     * Get diagnostic information about resolved components and views.
     */
    public static function getDiagnostics(): array
    {
        $diagnostics = [
            'config' => [
                'auto_discover' => config('filament-jetstream.auto_discover', true),
                'stubs_path' => config('filament-jetstream.stubs_path', app_path('Livewire/FilamentJetstream')),
                'views_path' => config('filament-jetstream.views_path', resource_path('views/livewire/filament-jetstream')),
            ],
            'components' => [],
            'views' => [],
        ];

        foreach (static::$componentMap as $key => $classPath) {
            $className = 'App\\Livewire\\FilamentJetstream\\' . $classPath;
            $filePath = app_path('Livewire/FilamentJetstream/' . str_replace('\\', '/', $classPath) . '.php');

            $diagnostics['components'][$key] = [
                'class_name' => $className,
                'file_path' => $filePath,
                'file_exists' => File::exists($filePath),
                'class_exists' => class_exists($className, false),
                'discovered' => static::discoverPublishedComponent($key) !== null,
                'config_override' => config("filament-jetstream.components.{$key}"),
            ];
        }

        foreach (static::$viewMap as $key => $viewPath) {
            $viewsBasePath = config('filament-jetstream.views_path', resource_path('views/livewire/filament-jetstream'));
            $filePath = $viewsBasePath . '/' . $viewPath . '.blade.php';

            $diagnostics['views'][$key] = [
                'view_name' => 'livewire.filament-jetstream.' . str_replace('/', '.', $viewPath),
                'file_path' => $filePath,
                'file_exists' => File::exists($filePath),
                'discovered' => static::discoverPublishedView($key) !== null,
            ];
        }

        return $diagnostics;
    }

    /**
     * Clear the resolver cache.
     */
    public static function clearCache(): void
    {
        static::$componentCache = [];
        static::$viewCache = [];
    }
}

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stubs Path
    |--------------------------------------------------------------------------
    |
    | This option controls the default path where published Livewire components
    | will be stored. When you publish stubs, they will be placed in this
    | directory relative to your application's base path.
    |
    */
    'stubs_path' => app_path('Livewire/FilamentJetstream'),

    /*
    |--------------------------------------------------------------------------
    | Views Path
    |--------------------------------------------------------------------------
    |
    | This option controls the default path where published component views
    | will be stored. Published blade views will be placed in this directory
    | relative to your application's resources path.
    |
    */
    'views_path' => resource_path('views/livewire/filament-jetstream'),

    /*
    |--------------------------------------------------------------------------
    | Auto Discovery
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will automatically discover and use published
    | components from the stubs_path instead of the package defaults. Set to
    | false if you want to manually control which components are overridden.
    |
    */
    'auto_discover' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Clearing
    |--------------------------------------------------------------------------
    |
    | Control whether to clear various caches when publishing stubs. This
    | helps ensure published components are discovered immediately.
    |
    */
    'clear_cache_on_publish' => true,

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | This array controls which features are available in your application.
    | You can enable or disable specific features as needed. These settings
    | work in conjunction with the JetstreamPlugin configuration.
    |
    */
    'features' => [
        'profile_photos' => true,
        'api_tokens' => false,
        'teams' => false,
        'two_factor_authentication' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Overrides
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom component classes to use instead of the
    | package defaults. Set to null to use the package default component.
    | This provides fine-grained control over component customization.
    |
    */
    'components' => [
        // Profile Components
        'profile.update_profile_information' => null,
        'profile.update_password' => null,
        'profile.delete_account' => null,
        'profile.logout_other_browser_sessions' => null,

        // Team Components
        'teams.update_team_name' => null,
        'teams.add_team_member' => null,
        'teams.team_members' => null,
        'teams.pending_team_invitations' => null,
        'teams.delete_team' => null,

        // API Token Components
        'api_tokens.create_api_token' => null,
        'api_tokens.manage_api_tokens' => null,
    ],
];

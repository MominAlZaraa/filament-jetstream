# Filament Jetstream — A Laravel Starter Kit Built With Filament

![Edit Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/banner.jpg)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stephenjude/filament-jetstream.svg?style=flat-square)](https://packagist.org/packages/stephenjude/filament-jetstream)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/stephenjude/filament-jetstream/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/stephenjude/filament-jetstream/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/stephenjude/filament-jetstream/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/stephenjude/filament-jetstream/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stephenjude/filament-jetstream.svg?style=flat-square)](https://packagist.org/packages/stephenjude/filament-jetstream)

Filament Jetstream, just like [Laravel Jetstream](https://jetstream.laravel.com/introduction.html) is a beautifully designed application starter kit for Laravel and provides the perfect starting point for your next Laravel application.

Includes auth, registration, 2FA, session management, API tokens, and team support, all implemented with **native Filament panels and components**. 

Skip boilerplate, start building features.

## Installation

You can install the package via composer:

```bash
composer require stephenjude/filament-jetstream

php artisan filament-jetstream:install --teams --api
```
You can remove the `--teams` and `--api` arguments if you don't want those features.

## Features

##### 🔐 Authentication
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/login.jpeg)

##### 👤 User Profile
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/profile.jpeg)

##### 👥 Team (Optional)
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/team.jpeg)

##### 🔑 API Tokens (Optional)
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/tokens.jpeg)

##### 🌍 Translation-ready

## Usage & Configurations

#### Configuring the User Profile
```php
use \App\Models\User;
use Filament\Jetstream\JetstreamPlugin;
use Illuminate\Validation\Rules\Password;

...
JetstreamPlugin::make()
    ->configureUserModel(userModel: User::class)
    ->profilePhoto(condition: fn() => true, disk: 'public')
    ->deleteAccount(condition: fn() => true)
    ->updatePassword(condition: fn() => true, Password::default())
    ->profileInformation(condition: fn() => true)
    ->logoutBrowserSessions(condition: fn() => true)
    ->twoFactorAuthentication(
        condition: fn() => auth()->check(),
        forced: fn() => app()->isProduction(),
        enablePasskey: fn() =>  Feature::active('passkey'),
        requiresPassword: fn() => app()->isProduction(),
    )
```

#### Configuring Team features

```php
use \Filament\Jetstream\Role;
use Filament\Jetstream\JetstreamPlugin;
use Illuminate\Validation\Rules\Password;
use \Filament\Jetstream\Models\{Team,Membership,TeamInvitation};

...
JetstreamPlugin::make()
    ->teams(
        condition: fn() => Feature::active('teams'), 
        acceptTeamInvitation: fn($invitationId) => JetstreamPlugin::make()->defaultAcceptTeamInvitation()
    )
    ->configureTeamModels(
        teamModel: Team::class,
        roleModel: Role::class,
        membershipModel: Membership::class,
        teamInvitationModel:  TeamInvitation::class
    )
```

#### Configuring API features
```php
use Filament\Jetstream\JetstreamPlugin;
use Illuminate\Validation\Rules\Password;
use \Filament\Jetstream\Role;
use \Filament\Jetstream\Models\{Team, Membership, TeamInvitation};

JetstreamPlugin::make()
    ->apiTokens(
        condition: fn() => Feature::active('api'), 
        permissions: fn() => ['create', 'read', 'update', 'delete'],
        menuItemLabel: fn() => 'API Tokens',
        menuItemIcon: fn() => 'heroicon-o-key',
    ),
```

## Existing Laravel projects

### Installing the Profile feature

#### Publish profile migrations
Run the following command to publish the profile migrations.

```bash
php artisan vendor:publish \
  --tag=filament-jetstream-migrations \
  --tag=passkeys-migrations \
  --tag=filament-two-factor-authentication-migrations
```

#### Add profile feature traits to the User model
Update the `App\Models\User` model:

```php
...
use Filament\Jetstream\HasProfilePhoto;
use Filament\Models\Contracts\HasAvatar;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use \Filament\Jetstream\InteractsWIthProfile;

class User extends Authenticatable implements  HasAvatar, HasPasskeys
{
    ...
    use InteractsWIthProfile;

    protected $hidden = [
        ...
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        ...
        'profile_photo_url',
    ];
}
```

### Installing the Team Features

#### Publish team migration
Run the following command to publish the **team** migrations.
```bash
php artisan vendor:publish --tag=filament-jetstream-team-migration
```

#### Add team feature traits to User model
Update `App\Models\User` model to implement 'Filament\Models\Contracts\HasTenants' and use `Filament\Jetstream\InteractsWithTeams` trait.

```php
...
use Filament\Jetstream\InteractsWithTeams;
use Filament\Models\Contracts\HasTenants;

class User extends Authenticatable implements  HasTenants
{
    ...
    use InteractsWithTeams;
}

```

### Installing the API Features
#### Publish team migration
Run the following command to publish the **team** migrations.
```bash
php artisan vendor:publish --tag=filament-jetstream-team-migration
```

#### Add api feature trait to User model
Update `App\Models\User` model to  use `Laravel\Sanctum\HasApiTokens` trait.
```php
...
use \Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable 
{
    use HasApiTokens;
}

```

## Customization

### Publishing and Customizing Components

Filament Jetstream allows you to fully customize authentication and profile-related forms by publishing Livewire component stubs.

#### Publishing Stubs

To publish all customizable components:

```bash
php artisan filament-jetstream:publish-stubs
```

To publish only specific groups:

```bash
# Publish only profile components
php artisan filament-jetstream:publish-stubs --only=profile

# Publish only team components
php artisan filament-jetstream:publish-stubs --only=teams

# Publish only API token components
php artisan filament-jetstream:publish-stubs --only=api

# Publish multiple groups
php artisan filament-jetstream:publish-stubs --only=profile,teams
```

To overwrite existing published files:

```bash
php artisan filament-jetstream:publish-stubs --force
```

#### Published File Structure

Published components will be placed in:

- **Livewire Components**: `app/Livewire/FilamentJetstream/`
  - `Profile/UpdateProfileInformation.php`
  - `Profile/UpdatePassword.php`
  - `Profile/DeleteAccount.php`
  - `Profile/LogoutOtherBrowserSessions.php`
  - `Teams/UpdateTeamName.php`
  - `Teams/AddTeamMember.php`
  - `Teams/TeamMembers.php`
  - `Teams/PendingTeamInvitations.php`
  - `Teams/DeleteTeam.php`
  - `ApiTokens/CreateApiToken.php`
  - `ApiTokens/ManageApiTokens.php`

- **Views**: `resources/views/livewire/filament-jetstream/`

#### Auto-Discovery

By default, when you publish components, Filament Jetstream will automatically use your published versions instead of the package defaults. This behavior is controlled in the config file:

```php
// config/filament-jetstream.php
'auto_discover' => true, // Set to false to disable auto-discovery
```

#### Manual Component Overrides

You can also manually specify which components to use in the config file:

```php
// config/filament-jetstream.php
'components' => [
    'profile.update_profile_information' => App\Livewire\Custom\UpdateProfile::class,
    'profile.update_password' => null, // Use package default
    // ...
],
```

#### Customizing Published Components

Each published component includes TODO comments showing where to add customizations:

```php
// app/Livewire/FilamentJetstream/Profile/UpdateProfileInformation.php

public function form(Schema $schema): Schema
{
    return $schema
        ->schema([
            Section::make(...)
                ->schema([
                    // Existing fields...
                    
                    // TODO: Add custom fields here
                    // Example:
                    // TextInput::make('phone')
                    //     ->label('Phone Number')
                    //     ->tel()
                    //     ->nullable(),
                ]),
        ]);
}
```

#### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=filament-jetstream-config
```

Available configuration options:

```php
// config/filament-jetstream.php
return [
    // Path where published components will be stored
    'stubs_path' => app_path('Livewire/FilamentJetstream'),
    
    // Path where published views will be stored
    'views_path' => resource_path('views/livewire/filament-jetstream'),
    
    // Auto-discover and use published components
    'auto_discover' => true,
    
    // Feature toggles
    'features' => [
        'profile_photos' => true,
        'api_tokens' => false,
        'teams' => false,
        'two_factor_authentication' => false,
    ],
    
    // Manual component overrides
    'components' => [
        'profile.update_profile_information' => null,
        // ...
    ],
];
```

#### Example: Adding a Phone Field to Profile

1. Publish the profile stubs:
   ```bash
   php artisan filament-jetstream:publish-stubs --only=profile
   ```

2. Edit `app/Livewire/FilamentJetstream/Profile/UpdateProfileInformation.php`:
   ```php
   TextInput::make('phone')
       ->label('Phone Number')
       ->tel()
       ->nullable(),
   ```

3. Add the phone field to your User model's `$fillable` array.

4. The changes will be automatically used by Filament Jetstream!

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [stephenjude](https://github.com/stephenjude)
- [taylorotwell](https://github.com/taylorotwell)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

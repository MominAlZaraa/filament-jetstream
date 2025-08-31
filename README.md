# Filament Jetstream — A Filament Starter Kit

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

## Existing Laravel projects

### Installing the Profile feature

#### Publish profile migrations
Run the following command to publish the profile migrations.

```bash
php artisan vendor:publish --tag=filament-jetstream-migrations --tag=passkeys-migrations --tag=filament-two-factor-authentication-migrations
```

#### Add profile feature traits to the User model
Update the `App\Models\User` model:

```php
...
use Filament\Jetstream\HasProfilePhoto;
use Filament\Models\Contracts\HasAvatar;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticatable;

class User extends Authenticatable implements  HasAvatar, HasPasskeys
{
    ...
    use HasProfilePhoto; 
    use TwoFactorAuthenticatable; 

    protected $hidden = [
        ...
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        ...
        'profile_photo_url',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }
}
```

#### Configure the profile features 
Add the following to your default panel plugin configuration:

```php
use Filament\Jetstream\JetstreamPlugin;use Illuminate\Validation\Rules\Password;
...
->plugins([
    ...
    JetstreamPlugin::make()
        ->configureUserModel(userModel: \App\Models\User::class)
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
        ),
])
```

### Installing the Team Features

#### Publish team migration
Run the following command to publish the **team** migrations.
```bash
php artisan vendor:publish --tag=filament-jetstream-team-migration
```

#### Add team feature traits to User model
Update `App\Models\User` model to implement 'Filament\Models\Contracts\HasTenants' and use `Filament\Jetstream\HasTeams` trait.

```php
use Filament\Models\Contracts\HasTenants;
use Filament\Jetstream\HasTeams;

class User extends Authenticatable implements  HasTenants
{
    ...
    use HasTeams;
}

```

#### Configure the team features
Add the following to your default panel plugin configuration:

```php
use Filament\Jetstream\JetstreamPlugin;
use Illuminate\Validation\Rules\Password;
use \Filament\Jetstream\Role;
use \Filament\Jetstream\Models\{Team,Membership,TeamInvitation};
...
->plugins([
    ...
    JetstreamPlugin::make()
        ->teams(
            condition: fn() => Feature::active('teams'), 
            acceptTeamInvitation: fn($invitationId) => TeamInvitation::find($invitationId)->accept() // write your logic here
        )
        ->configureTeamModels(
            teamModel: Team::class,
            roleModel: Role::class,
            membershipModel: Membership::class,
            teamInvitationModel:  TeamInvitation::class
        ),
])
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


use Filament\Models\Contracts\HasTenants;
use Filament\Jetstream\HasTeams;

class User extends Authenticatable implements  HasTenants
{
    use HasTeams;
}

```

#### Configure the API features
Add the following to your default panel plugin configuration:

```php
use Filament\Jetstream\JetstreamPlugin;
use Illuminate\Validation\Rules\Password;
use \Filament\Jetstream\Role;
use \Filament\Jetstream\Models\{Team, Membership, TeamInvitation};
...
->plugins([
    ...
    JetstreamPlugin::make()
        ->apiTokens(
            condition: fn() => Feature::active('api'), 
            permissions: fn() => ['create', 'read', 'update', 'delete'],
            menuItemLabel: fn() => 'API Tokens',
            menuItemIcon: fn() => 'heroicon-o-key',
        ),
])
```


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

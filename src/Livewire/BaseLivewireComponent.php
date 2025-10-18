<?php

namespace Filament\Jetstream\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Exception;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

abstract class BaseLivewireComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithRateLimiting;

    /**
     * Get the view name for this component, checking for published views first.
     */
    protected function getViewName(string $defaultView): string
    {
        // Extract the component key from the default view
        // e.g., "filament-jetstream::livewire.profile.update-profile-information"
        // becomes "profile.update_profile_information"
        $key = str_replace(
            ['filament-jetstream::livewire.', '-'],
            ['', '_'],
            $defaultView
        );

        // Try to discover a published view
        $publishedView = \Filament\Jetstream\ComponentResolver::discoverPublishedView($key);

        return $publishedView ?? $defaultView;
    }

    public function authUser(): FilamentUser | Model | Authenticatable
    {
        /** @var FilamentUser $user */
        $user = Filament::auth()->user();

        if (! $user instanceof FilamentUser) {
            throw new Exception('The authenticated user object must be a filament auth model!');
        }

        return $user;
    }

    protected function sendRateLimitedNotification(TooManyRequestsException $exception): void
    {
        Notification::make()
            ->title(__('filament-jetstream::default.notification.rate_limited.title'))
            ->body(__('filament-jetstream::default.notification.rate_limited.message', ['seconds' => $exception->secondsUntilAvailable]))
            ->danger()
            ->send();
    }

    protected function sendNotification(string $title = 'Saved', ?string $message = null, string $type = 'success'): void
    {
        Notification::make()
            ->title(__($title))
            ->body(__($message))
            ->{$type}()
            ->send();
    }
}

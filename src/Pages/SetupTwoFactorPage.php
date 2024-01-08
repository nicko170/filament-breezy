<?php

namespace Jeffgreco13\FilamentBreezy\Pages;

use Filament\Forms;
use Filament\Actions\Action;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Support\Collection;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Pages\Concerns\InteractsWithFormActions;

use Livewire\Attributes\Url;

class SetupTwoFactorPage extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    protected static string $view = 'filament-breezy::filament.pages.setup-two-factor';

    public $user;
    public $code;
    public bool $showRecoveryCodes = false;

    #[Url]
    public ?string $next = null;

    public function getTitle(): string
    {
        return __('filament-breezy::default.profile.2fa.title');
    }

    public function getSubheading(): string
    {
        return __('filament-breezy::default.profile.2fa.must_enable');
    }

    public function mount()
    {
        if (!Filament::auth()->check()) {
            return redirect()->to(Filament::getLoginUrl());
        } else if (filament('filament-breezy')->auth()->user()->hasValidTwoFactorSession()) {
            return redirect()->to(Filament::getHomeUrl());
        }

        $user = Filament::auth()->user();
        if (!$user->hasEnabledTwoFactor()) {
            $user->enableTwoFactorAuthentication();
        }

        $this->user = $user;
    }

    public function getTwoFactorQrCode()
    {
        return filament('filament-breezy')->getTwoFactorQrCodeSvg($this->user->getTwoFactorQrCodeUrl());
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getRecoveryCodesProperty(): Collection
    {
        return collect($this->user->two_factor_recovery_codes ?? []);
    }


    public function confirmAction(): Action
    {
        return Action::make('confirm')
            ->color('primary')
            ->label(__('filament-breezy::default.profile.2fa.actions.confirm_finish'))
            ->modalWidth('sm')
            ->form([
                Forms\Components\TextInput::make('code')
                    ->label(__('filament-breezy::default.fields.2fa_code'))
                    ->placeholder('###-###')
                    ->required()
            ])
            ->action(function ($data, $action, $livewire) {
                if (!filament('filament-breezy')->verify(code: $data['code'])) {
                    $livewire->addError('mountedActionsData.0.code', __('filament-breezy::default.profile.2fa.confirmation.invalid_code'));
                    $action->halt();
                }
                $this->user->confirmTwoFactorAuthentication();
                $this->user->setTwoFactorSession();
                Notification::make()
                    ->success()
                    ->title(__('filament-breezy::default.profile.2fa.confirmation.success_notification'))
                    ->send();

                if ($this->next) {
                    return redirect()->to($this->next);
                }

                return redirect()->to(Filament::getHomeUrl());
            });
    }
}

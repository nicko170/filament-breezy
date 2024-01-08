<x-filament-panels::page.simple>
    <p class="text-sm">{{ __('filament-breezy::default.profile.2fa.finish_enabling.description') }}</p>

    <div>
        {!! $this->getTwoFactorQrCode() !!}
        <p class="pt-2 text-sm">{{ __('filament-breezy::default.profile.2fa.setup_key') }} {{
                            decrypt($this->user->two_factor_secret) }}</p>
    </div>

    <div>
        <p class="text-xs">{{ __('filament-breezy::default.profile.2fa.enabled.store_codes') }}</p>

        <div>
            @foreach ($this->recoveryCodes->toArray() as $code )
                <span
                    class="inline-flex items-center p-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-900">{{ $code }}</span>
            @endforeach
        </div>

        <div class="inline-block text-xs">
            <x-filament-breezy::clipboard-link :data="$this->recoveryCodes->join(',')"/>
        </div>

    </div>

    <div class="flex justify-between mt-3">
        {{ $this->confirmAction }}
    </div>
</x-filament-panels::page.simple>

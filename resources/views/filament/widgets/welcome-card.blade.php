<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <x-filament-panels::avatar.user
            size="lg"
            :user="$user"
            loading="lazy"
        />

        <div class="fi-account-widget-main">
            <h2 class="fi-account-widget-heading">
                Selamat Datang
            </h2>

            <p class="fi-account-widget-user-name">
                {{ $userName }}
            </p>
        </div>

        <form
            action="{{ $logoutUrl }}"
            method="post"
            class="fi-account-widget-logout-form"
        >
            @csrf

            <x-filament::button
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::ArrowLeftEndOnRectangle"
                :icon-alias="\Filament\View\PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON"
                labeled-from="sm"
                tag="button"
                type="submit"
            >
                {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>

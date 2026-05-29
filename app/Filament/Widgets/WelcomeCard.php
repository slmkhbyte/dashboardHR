<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class WelcomeCard extends Widget
{
    protected static ?int $sort = -10;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.welcome-card';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = Filament::auth()->user();

        return [
            'appName' => config('app.name'),
            'logoutUrl' => filament()->getLogoutUrl(),
            'user' => $user,
            'userName' => filament()->getUserName($user),
        ];
    }
}

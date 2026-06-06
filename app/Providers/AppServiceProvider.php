<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\HguMarker;
use App\Observers\EmployeeDocumentObserver;
use App\Observers\EmployeeObserver;
use App\Observers\HguMarkerObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Employee::observe(EmployeeObserver::class);
        EmployeeDocument::observe(EmployeeDocumentObserver::class);
        HguMarker::observe(HguMarkerObserver::class);

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}

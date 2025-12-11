<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
class AppServiceProvider extends ServiceProvider
{
    
    /**
     * Bootstrap any application services.
     */
    
    public function boot(): void
    {
        $this->app['router']->aliasMiddleware('IsAdmin', \App\Http\Middleware\IsAdmin::class);
        date_default_timezone_set('Indian/Antananarivo');

        // Carbon (utilisé partout : created_at, dates, etc.) suit aussi Madagascar
        Carbon::setLocale('fr');
    
    }
}

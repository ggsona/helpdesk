<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\View\Components\AdminLayout;
use App\View\Components\ClienteLayout;
use Illuminate\Support\Facades\DB;

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
        // Ensure the application uses the same timezone globally
        date_default_timezone_set(config('app.timezone'));
        DB::statement("SET time_zone = '-04:00'"); // Align MySQL session with app timezone (offset)
        Blade::component('cliente-layout', ClienteLayout::class);
        // Ensure MySQL uses the same timezone as the application (optional, disabled to avoid errors)
        // \Illuminate\Support\Facades\DB::statement("SET time_zone = '" . config('app.timezone') . "'"); // Disabled to avoid MySQL timezone errors
    }
}

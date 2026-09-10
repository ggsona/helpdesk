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

        // Align TiDB/MySQL session timezone with app timezone.
        // Wrapped in try/catch to avoid crashes during artisan commands
        // that run without DB access (e.g. package:discover during Docker build).
        try {
            DB::statement("SET time_zone = '-04:00'");
        } catch (\Exception $e) {
            // No DB available (build phase or CLI) — safe to ignore
        }

        Blade::component('cliente-layout', ClienteLayout::class);
        // Ensure MySQL uses the same timezone as the application (optional, disabled to avoid errors)
        // \Illuminate\Support\Facades\DB::statement("SET time_zone = '" . config('app.timezone') . "'"); // Disabled to avoid MySQL timezone errors
    }
}

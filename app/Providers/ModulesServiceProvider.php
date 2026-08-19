<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
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
        $modulesPath = app_path('Modules');

        if (!File::isDirectory($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {
            $moduleName = basename($module);

            // Register Web Routes
            $webRoutes = $module . '/routes/web.php';
            if (File::exists($webRoutes)) {
                Route::middleware('web')
                    ->group($webRoutes);
            }

            // Register API Routes
            $apiRoutes = $module . '/routes/api.php';
            if (File::exists($apiRoutes)) {
                Route::prefix('api')
                    ->middleware('api')
                    ->group($apiRoutes);
            }

            // Register Views if present
            $viewsPath = $module . '/resources/views';
            if (File::isDirectory($viewsPath)) {
                $this->loadViewsFrom($viewsPath, strtolower($moduleName));
            }

            // Register Translations if present
            $translationsPath = $module . '/resources/lang';
            if (File::isDirectory($translationsPath)) {
                $this->loadTranslationsFrom($translationsPath, strtolower($moduleName));
            }

            // Register Migrations if present
            $migrationsPath = $module . '/database/migrations';
            if (File::isDirectory($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }
}

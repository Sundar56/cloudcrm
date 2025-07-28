<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;


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
        parent::boot();
        // Dynamically load module routes
        $this->loadModuleApi();
    }
    protected function loadModuleApi(): void
    {
        $systemadminModules  = File::directories(app_path('Api/Systemadmin/Modules'));
        $customerModules = File::directories(app_path('Api/Customer/Modules'));
        $permissionModules = File::directories(app_path('Api/Commanapi/Modules'));

        // Combine both directories
        $modules = array_merge($systemadminModules, $customerModules, $permissionModules);
        foreach ($modules as $modulePath) {
            $module = basename($modulePath);
            $routesPath = $modulePath . '/routes.php';
            if (file_exists($routesPath)) {
                Route::prefix('api')->middleware('api')
                    ->group(function () use ($routesPath) {
                        require $routesPath;
                    });
            }
        }
    }
}

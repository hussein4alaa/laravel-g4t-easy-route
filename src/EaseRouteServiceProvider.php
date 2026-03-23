<?php

namespace G4T\EaseRoute;

use Illuminate\Support\ServiceProvider;

class EaseRouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/route-attribute.php' => config_path('route-attribute.php'),
        ], 'config');

        $cachePath = base_path('bootstrap/cache/routes-v7.php');

        if (config('route-attribute.cache', true) && file_exists($cachePath)) {
            include $cachePath;
        } else {
            $paths = config('route-attribute.controllers_path', [app_path('Http/Controllers')]);
            if (!is_array($paths)) $paths = [$paths];
            foreach ($paths as $path) {
                RouteRegistrar::scanAndRegister($path);
            }
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/route-attribute.php', 'route-attribute');
    }
}
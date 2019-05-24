<?php

namespace Kolirt\Translations;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $commands = [
        Commands\InstallCommand::class
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        $this->mergeConfigFrom(__DIR__ . '/../config/translations.php', 'translations');

        $this->publishes([
            __DIR__ . '/../config/translations.php' => config_path('translations.php')
        ]);
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->commands($this->commands);
    }
}
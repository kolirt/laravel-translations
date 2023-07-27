<?php

namespace Kolirt\Translations;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        if (config('translations.connection') === config('database.default', 'mysql')) {
            $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/translations.php', 'translations');

        $this->publishes([
            __DIR__ . '/../config/translations.php' => config_path('translations.php')
        ]);

        Validator::extend('unique_loc', function ($attribute, $value, $parameters) {
            $lang = last(explode('.', $attribute));
            if (!in_array($lang, config('translations.locales', [])))
                throw new \Exception('Lang not exist in translations.locales config');

            $table = $parameters[0];
            $type = $parameters[1];
            $key = $parameters[2];
            $id = $parameters[3] ?? null;

            $validator = Validator::make([
                $type => $value
            ], [
                $type => [
                    Rule::unique(config('translations.connection') . '.translations')->ignore($id, 'translation_id')->where('lang', $lang)->where('translation_type', $table)->where('key', $key)
                ]
            ]);

            return !$validator->fails();
        }, __('validation.unique'));
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->commands($this->commands);
    }
}
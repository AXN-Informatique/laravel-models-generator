<?php

namespace Axn\ModelsGenerator;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['command.models.generate'] = $this->app->share(function() {
            return new Console\GenerateCommand;
        });

        $this->commands([
            'command.models.generate'
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/models-generator.php' => config_path('models-generator.php')
        ], 'models-generator.config');

        $this->publishes([
            __DIR__.'/../resources/stubs/' => base_path('resources/stubs/vendor/models-generator')
        ], 'models-generator.stubs');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.models.generate'
        ];
    }
}

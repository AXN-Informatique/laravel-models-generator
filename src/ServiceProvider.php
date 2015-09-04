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

        $this->app['command.models.list'] = $this->app->share(function() {
            return new Console\ListCommand;
        });

        $this->commands([
            'command.models.generate',
            'command.models.list'
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
            __DIR__.'/../config/models-generator.php' => config_path('models-generator.php'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.models.generate',
            'command.models.list'
        ];
    }
}

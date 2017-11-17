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
        $this->app->singleton('command.models.generate', function($app) {
            $db = $app['db.connection'];
            $driverClass = __NAMESPACE__.'\Drivers\\'.ucfirst($db->getDriverName()).'Driver';
            $driver = new $driverClass($db->getPdo());

            $builder = new Builder($app['config'], $driver);

            return new Console\GenerateCommand($builder);
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

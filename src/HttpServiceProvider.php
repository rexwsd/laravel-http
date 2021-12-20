<?php

namespace Laravel\Http;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
class HttpServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $source = realpath($raw = __DIR__.'/config/http.php') ?: $raw;

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('http.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('http');
        }
        $this->mergeConfigFrom($source, 'http');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('http', function () {
            return new HttpManager($this->app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['http'];
    }
}

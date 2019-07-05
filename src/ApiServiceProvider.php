<?php

namespace DevLabor\Api;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
			__DIR__ . '/config/api.php' => config_path('api.php'),
		]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
		$this->app->make('DevLabor\Api\Http\Controller\ApiController');
    }
}

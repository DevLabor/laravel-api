<?php

namespace DevLabor\Api\Tests;

use DevLabor\Api\ApiServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Setting up.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/database/factories');

        $this->setUpDatabase($this->app);
        $this->setUpRoutes($this->app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array|string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            ApiServiceProvider::class,
        ];
    }

    /**
     * @param $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('description');
        });
    }

    /**
     * @param $app
     */
    protected function setUpRoutes($app)
    {
        Route::prefix('api')
            ->name('api.')
            ->namespace('DevLabor\Api\Tests\Http\Controllers')
            ->group(function () {
                Route::resource('projects', 'ProjectApiController');
            });
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}

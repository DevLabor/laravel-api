<?php

namespace DevLabor\Api\Tests;

use DevLabor\Api\ApiServiceProvider;
use DevLabor\Api\Tests\Http\Controllers\ProjectApiController;
use Illuminate\Database\Schema\Blueprint;
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
            ->group(function () {
                Route::resource('projects', ProjectApiController::class);
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

        // default query-builder config
        $app['config']->set('query-builder', [
            'parameters' => [
                'include' => 'include',
                'filter' => 'filter',
                'sort' => 'sort',
                'fields' => 'fields',
                'append' => 'append',
            ],
            'count_suffix' => 'Count',
            'disable_invalid_filter_query_exception' => false,
            'request_data_source' => 'query_string',
        ]);
    }
}

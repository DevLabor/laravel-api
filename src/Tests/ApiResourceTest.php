<?php

namespace DevLabor\Api\Tests;

use Illuminate\Http\Response;
use Illuminate\Support\Str;

trait ApiResourceTest
{
    /**
     * Guessed routeName.
     * @var string
     */
    protected $routeName = '';

    /**
     * Prefix for api route.
     * @var string
     */
    protected $routeApiPrefix = 'api';

    /**
     * Payload.
     * @var array
     */
    protected $payload = [];

    /**
     * Guess model class name.
     *
     * @return null|string
     */
    protected function guessModelClass()
    {
        if (empty($this->modelClass)) {
            $this->modelClass = 'App\\' . str_replace('ApiResourceTest', '', class_basename(get_class($this)));
        }

        return $this->modelClass;
    }

    /**
     * Guess factory name.
     *
     * @return null|string
     */
    protected function guessFactoryClass()
    {
        if (empty($this->factoryClass)) {
            $this->factoryClass = $this->guessModelClass();
        }

        return $this->factoryClass;
    }

    /**
     * Guess routeName.
     *
     * @return null|string
     */
    protected function guessRouteName()
    {
        if (empty($this->routeName)) {
            $this->routeName = Str::snake(Str::plural(class_basename($this->guessModelClass())));
        }

        return $this->routeName;
    }

    /**
     * Guess routeName.
     *
     * @return null|string
     */
    protected function guessRoute($action, $parameters = [])
    {
        return route(($this->routeApiPrefix ? $this->routeApiPrefix . '.' : '') . $this->guessRouteName() . '.' . $action, $parameters);
    }

    /**
     * Enable data by factory class.
     *
     * @return bool
     */
    protected function useFactory()
    {
        return true;
    }

    /**
     * Returns payload for store, update or destroy.
     *
     * @return array
     */
    protected function getPayload()
    {
        if ($this->useFactory()) {
            return $this->guessModelClass()::factory()->raw();
        }

        return $this->payload;
    }

    /**
     * Create one instance of a model using a factory.
     *
     * @return int id of new model instance
     */
    protected function createModel()
    {
        return $this->guessModelClass()::factory()->create()->id;
    }

    /**
     * Testing index route.
     */
    public function testIndex()
    {
        $response = $this->json('GET', $this->guessRoute('index'));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    /**
     * Testing store route
     */
    public function testStore()
    {
        $response = $this->json('POST', $this->guessRoute('store'), $this->getPayload());

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
            ])
            ->assertJson([
                '_model' => [
                    'endpoint' => $this->guessRouteName(),
                ],
            ]);
    }

    /**
     * Testing show route.
     */
    public function testShow()
    {
        $id = $this->createModel();

        $response = $this->json('GET', $this->guessRoute('show', [ $id ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                '_model' => [
                    'endpoint' => $this->guessRouteName(),
                ],
            ]);
    }

    /**
     * Testing store route
     */
    public function testUpdate()
    {
        $id = $this->createModel();

        $response = $this->json('PUT', $this->guessRoute('update', [ $id ]), $this->getPayload());

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                '_model' => [
                    'endpoint' => $this->guessRouteName(),
                ],
            ]);
    }

    /**
     * Testing destroy route
     */
    public function testDestroy()
    {
        $id = $this->createModel();

        $response = $this->json('DELETE', $this->guessRoute('destroy', [ $id ]));

        $response
            ->assertStatus(Response::HTTP_OK);
    }
}

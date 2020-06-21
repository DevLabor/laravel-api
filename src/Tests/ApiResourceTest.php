<?php

namespace DevLabor\Api\Tests;

use Illuminate\Http\Response;
use Illuminate\Support\Str;

trait ApiResourceTest
{
    /**
     * Last insert id.
     * @var int|null
     */
    public static $lastInsert = null;

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
            return factory($this->guessFactoryClass())->raw();
        }

        return $this->payload;
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

        $data = $response->json();

        if (isset($data['id'])) {
            self::$lastInsert = $data['id'];
        }

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
        $response = $this->json('GET', $this->guessRoute('show', [ self::$lastInsert ]));

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
        $response = $this->json('PUT', $this->guessRoute('update', [ self::$lastInsert ]), $this->getPayload());

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
        $response = $this->json('DELETE', $this->guessRoute('destroy', [ self::$lastInsert ]));

        $response
            ->assertStatus(Response::HTTP_OK);
    }
}

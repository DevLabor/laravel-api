<?php

namespace DevLabor\Api\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ApiResource extends JsonResource
{
    /**
     * @var string
     */
    protected $forcedModelName = '';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->transformAttributes(parent::toArray($request));
    }

    /**
     * Transforms attributes
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function transformAttributes(array $attributes = [])
    {
        $snakeName = Str::snake($this->forcedModelName ? : class_basename($this->resource));

        $additionalAttributes = [
            '_model' => [
                'endpoint' => Str::plural($snakeName),
                'name' => $snakeName,
            ],
        ];

        return array_merge(
            $attributes,
            $additionalAttributes
        );
    }
}

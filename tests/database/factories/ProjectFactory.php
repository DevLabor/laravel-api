<?php

namespace DevLabor\Api\Tests\Database\Factories;

use DevLabor\Api\Tests\Model\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
        ];
    }
}

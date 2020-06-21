<?php

use \Faker\Generator;

/* @var Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\DevLabor\Api\Tests\Model\Project::class, function (Generator $faker) {
    return [
        'title' => $faker->word(),
        'description' => $faker->text(),
    ];
});

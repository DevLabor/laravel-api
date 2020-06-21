<?php

namespace DevLabor\Api\Tests;

use DevLabor\Api\Tests\Model\Project;

class ApiResourceTraitTest extends TestCase
{
    use ApiResourceTest;

    /**
     * @var string
     */
    protected $modelClass = Project::class;

    // @todo test overwriting factoryClass
}

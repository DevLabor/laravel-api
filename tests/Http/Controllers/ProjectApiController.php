<?php

namespace DevLabor\Api\Tests\Http\Controllers;

use DevLabor\Api\Http\Controllers\ApiController;
use DevLabor\Api\Tests\Http\Resources\Project as ProjectResource;
use DevLabor\Api\Tests\Model\Project;

class ProjectApiController extends ApiController
{
    /**
     * @var array
     */
    protected $authorizeAbilities = [];

    /**
     * @var string
     */
    protected $modelClass = Project::class;

    /**
     * @var string
     */
    protected $resourceClass = ProjectResource::class;

    /**
     * @var string[]
     */
    protected $validationRules = [
        'title' => 'required',
        'description' => 'required',
    ];

    /**
     * ProjectApiController constructor.
     */
    public function __construct()
    {
        $this->middleware([]);
    }
}

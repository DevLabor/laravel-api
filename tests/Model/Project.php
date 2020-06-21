<?php

namespace DevLabor\Api\Tests\Model;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}

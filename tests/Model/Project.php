<?php

namespace DevLabor\Api\Tests\Model;

use DevLabor\Api\Tests\Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

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

    /** @return Factory<static>  */
    protected static function newFactory()
    {
        return ProjectFactory::new();
    }
}

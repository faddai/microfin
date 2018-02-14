<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/04/2017
 * Time: 22:34
 */

namespace App\Scopes;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderByCreatedAtScope implements Scope
{
    /**
     * @var string
     */
    private $direction;

    /**
     * OrderByCreatedAtScope constructor.
     * @param string $direction
     */
    public function __construct(string $direction = 'ASC')
    {
        $this->direction = $direction;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->orderBy('created_at', $this->direction);
    }
}
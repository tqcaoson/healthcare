<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class ApiFilter
{
    abstract public function apply(
        Builder $query,
        array $filters
    ): Builder;
}
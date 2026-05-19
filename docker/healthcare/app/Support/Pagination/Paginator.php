<?php

namespace App\Support\Pagination;

use Illuminate\Database\Eloquent\Builder;

class Paginator
{
    public static function paginate(
        Builder $query,
        int $default = 15
    ) {
        return $query->paginate(
            request()->integer(
                'per_page',
                $default
            )
        );
    }
}
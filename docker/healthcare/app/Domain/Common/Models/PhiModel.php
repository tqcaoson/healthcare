<?php

namespace App\Domain\Common\Models;

use App\Events\PhiAccessed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class PhiModel extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::retrieved(function (self $model) {
            event(new PhiAccessed($model));
        });

        static::deleting(function (self $model) {
            if (! $model->isForceDeleting()) {
                return;
            }

            throw new \RuntimeException(
                'Hard delete của PHI records không được phép.'
            );
        });
    }
}
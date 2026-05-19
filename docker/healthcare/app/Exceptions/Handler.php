<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'current_password',
    ];

    public function register(): void
    {
        $this->reportable(function (
            Throwable $e
        ) {
            //
        });

        $this->renderable(function (
            Throwable $e,
            $request
        ) {

            if ($request->expectsJson()) {

                return response()->json([
                    'message' => $e->getMessage(),
                ], 500);
            }
        });
    }
}
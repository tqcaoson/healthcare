<?php

namespace App\Http\Controllers\SmartOnFhir;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmartOnFhir\SmartTokenService;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function __invoke(
        Request $request,
        SmartTokenService $service
    ) {

        $user = User::query()->first();

        $token = $service->issue(
            $user,
            explode(
                ' ',
                $request->scope ?? ''
            )
        );

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }
}
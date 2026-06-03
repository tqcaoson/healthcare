<?php

namespace App\Http\Controllers\SmartOnFhir;

use App\Http\Controllers\Controller;
use App\Services\SmartOnFhir\SmartTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenController extends Controller
{
    /** Allowed SMART scopes this server will grant. */
    private const ALLOWED_SCOPES = [
        'patient/Patient.read',
        'patient/Observation.read',
        'patient/MedicationRequest.read',
        'user/Patient.read',
        'user/Observation.read',
        'openid',
        'profile',
        'offline_access',
    ];

    public function __invoke(
        Request $request,
        SmartTokenService $service
    ): JsonResponse {

        $user = Auth::user();

        if (! $user) {
            return response()->json(
                ['error' => 'unauthenticated'],
                401
            );
        }

        $requestedScopes = array_filter(
            explode(' ', $request->input('scope', '')),
            fn (string $s) => in_array($s, self::ALLOWED_SCOPES, true)
        );

        if (empty($requestedScopes)) {
            return response()->json(
                ['error' => 'invalid_scope'],
                400
            );
        }

        $token = $service->issue($user, array_values($requestedScopes));

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type'   => 'Bearer',
            'scope'        => implode(' ', $requestedScopes),
        ]);
    }
}
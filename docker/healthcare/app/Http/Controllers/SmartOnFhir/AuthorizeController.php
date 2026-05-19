<?php

namespace App\Http\Controllers\SmartOnFhir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthorizeController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'authorization_pending' => false,
            'client_id' => $request->client_id,
        ]);
    }
}
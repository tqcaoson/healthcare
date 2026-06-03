<?php

namespace App\Http\Controllers\SmartOnFhir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthorizeController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'client_id'     => 'required|string',
            'redirect_uri'  => 'required|url',
            'response_type' => 'required|in:code',
            'scope'         => 'required|string',
        ]);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // Consent screen — in production replace with a real consent view
        // that records user approval before issuing an authorization code.
        return response()->json([
            'authorization_pending' => true,
            'message' => 'User consent required before authorization is granted.',
        ], 202);
    }
}
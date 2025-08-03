<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Client;

class SimpleAuth
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token || !str_starts_with($token, 'simple-token-')) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $clientId = str_replace('simple-token-', '', $token);
        $client = Client::find($clientId);
        
        if (!$client) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
        
        $request->merge(['auth_user' => $client]);
        return $next($request);
    }
}
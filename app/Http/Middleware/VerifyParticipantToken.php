<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Participant;

class VerifyParticipantToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = null;
        $authorizationHeader = $request->header('Authorization');

        if ($authorizationHeader && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            $token = $matches[1];
        } else {
            $allInputs = array_merge($request->all(), $request->json() ? $request->json()->all() : []);
            $token = $allInputs['token'] ?? $allInputs['_auth'] ?? $allInputs['auth_token'] ?? null;
        }

        $token = trim((string) $token);

        if (empty($token)) {
            return response()->json(['success' => false, 'message' => 'Authorization token is missing'], 401);
        }

        $participant = Participant::with('batch')->where('token', $token)->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired auth token'], 401);
        }
        $request->participant = $participant;
        return $next($request);
    }
}

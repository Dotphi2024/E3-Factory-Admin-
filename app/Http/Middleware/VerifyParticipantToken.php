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
            $token = $request->input('_auth') ?? $request->input('token') ?? $request->input('auth_token');
        }

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $participant = Participant::with('batch')->where('token', $token)->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $request->participant = $participant;
        return $next($request);
    }
}

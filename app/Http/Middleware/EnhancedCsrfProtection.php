<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

class EnhancedCsrfProtection
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip CSRF for GET requests and safe methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // Check if CSRF token is valid
        if (!$this->tokensMatch($request)) {
            // Log the CSRF failure for debugging
            \Log::warning('CSRF token mismatch', [
                'url' => $request->url(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'session_token' => $request->session()->token(),
                'request_token' => $this->getTokenFromRequest($request),
            ]);

            throw new TokenMismatchException('CSRF token mismatch.');
        }

        return $next($request);
    }

    /**
     * Determine if the session and input CSRF tokens match.
     */
    protected function tokensMatch(Request $request): bool
    {
        $token = $this->getTokenFromRequest($request);

        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }

    /**
     * Get the CSRF token from the request.
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            try {
                $token = decrypt($header);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $token;
    }
}

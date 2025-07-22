<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class HandleCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Enhanced CSRF validation with detailed logging
            $this->validateCsrfToken($request);
            
            $response = parent::handle($request, $next);
            
            // Add fresh CSRF token to response headers
            if ($response instanceof \Illuminate\Http\Response || 
                $response instanceof \Illuminate\Http\JsonResponse) {
                $response->header('X-CSRF-TOKEN', csrf_token());
                $response->header('X-CSRF-REFRESH', 'true');
            }
            
            return $response;
        } catch (TokenMismatchException $e) {
            Log::warning('CSRF Token Mismatch', [
                'url' => $request->url(),
                'method' => $request->method(),
                'session_token' => $request->session()->token(),
                'request_token' => $this->getTokenFromRequest($request),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
            
            // For Inertia requests, return JSON response
            if ($request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'CSRF token mismatch.',
                    'csrf_token' => csrf_token()
                ], 419);
            }
            
            throw $e;
        }
    }

    /**
     * Validate CSRF token with enhanced checks
     *
     * @param Request $request
     * @return void
     */
    protected function validateCsrfToken($request)
    {
        // Skip validation for excluded routes
        if ($this->inExceptArray($request)) {
            return;
        }

        // Skip validation for safe methods
        if ($this->isReading($request)) {
            return;
        }

        // Enhanced token validation
        if (!$this->tokensMatch($request)) {
            $this->handleTokenMismatch($request);
        }
    }

    /**
     * Handle token mismatch with detailed logging
     *
     * @param Request $request
     * @return void
     * @throws TokenMismatchException
     */
    protected function handleTokenMismatch($request)
    {
        $sessionToken = $request->session()->token();
        $requestToken = $this->getTokenFromRequest($request);
        
        Log::error('CSRF Token Validation Failed', [
            'url' => $request->url(),
            'method' => $request->method(),
            'session_token_exists' => !empty($sessionToken),
            'request_token_exists' => !empty($requestToken),
            'session_token_length' => strlen($sessionToken ?? ''),
            'request_token_length' => strlen($requestToken ?? ''),
            'headers' => [
                'X-CSRF-TOKEN' => $request->header('X-CSRF-TOKEN'),
                'X-XSRF-TOKEN' => $request->header('X-XSRF-TOKEN'),
                'X-Inertia' => $request->header('X-Inertia'),
            ],
            'session_id' => $request->session()->getId(),
        ]);
        
        throw new TokenMismatchException('CSRF token mismatch.');
    }

    /**
     * Enhanced token matching with multiple fallbacks
     *
     * @param Request $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $sessionToken = $request->session()->token();
        $requestToken = $this->getTokenFromRequest($request);

        if (!is_string($sessionToken) || !is_string($requestToken)) {
            return false;
        }

        return hash_equals($sessionToken, $requestToken);
    }

    /**
     * Get CSRF token from request with multiple sources
     *
     * @param Request $request
     * @return string|null
     */
    protected function getTokenFromRequest($request)
    {
        // Try form input first
        $token = $request->input('_token');
        
        // Try X-CSRF-TOKEN header
        if (!$token) {
            $token = $request->header('X-CSRF-TOKEN');
        }
        
        // Try encrypted X-XSRF-TOKEN header
        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            try {
                $token = $this->encrypter->decrypt($header, static::serialized());
            } catch (\Exception $e) {
                Log::warning('Failed to decrypt XSRF token', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $token;
    }
}

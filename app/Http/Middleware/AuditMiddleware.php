<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\AuditHelper;
use App\Models\AuditLog;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Get request data before processing
        $requestData = $this->getRequestData($request);
        
        // Process the request
        $response = $next($request);
        
        // Log the request after processing
        $this->logRequest($request, $response, $requestData, $startTime);
        
        return $response;
    }

    /**
     * Log the request
     */
    protected function logRequest(Request $request, Response $response, array $requestData, float $startTime): void
    {
        // Skip logging for certain routes
        if ($this->shouldSkipLogging($request)) {
            return;
        }

        $processingTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds
        $statusCode = $response->getStatusCode();
        
        // Determine severity based on status code and method
        $severity = $this->determineSeverity($request, $statusCode);
        
        // Skip info-level logs for GET requests unless they're sensitive
        if ($severity === 'info' && $request->method() === 'GET' && !$this->isSensitiveRoute($request)) {
            return;
        }

        AuditLog::createEntry([
            'event' => 'http_request',
            'auditable_type' => null,
            'auditable_id' => null,
            'module' => $this->getModuleFromRoute($request),
            'action' => strtolower($request->method()) . '_request',
            'description' => $this->getRequestDescription($request, $statusCode),
            'severity' => $severity,
            'tags' => $this->getRequestTags($request, $statusCode),
            'metadata' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'status_code' => $statusCode,
                'processing_time_ms' => $processingTime,
                'request_size' => strlen($request->getContent()),
                'response_size' => $response->headers->get('Content-Length'),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'request_data' => $requestData,
            ],
        ]);
    }

    /**
     * Get sanitized request data
     */
    protected function getRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remove sensitive fields
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'token',
            'api_key',
            'secret',
            'private_key',
            'credit_card',
            'ssn',
            'social_security',
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        
        // Limit data size to prevent huge logs
        $jsonData = json_encode($data);
        if (strlen($jsonData) > 5000) {
            return ['message' => 'Request data too large to log', 'size' => strlen($jsonData)];
        }
        
        return $data;
    }

    /**
     * Determine if logging should be skipped
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        $skipRoutes = [
            'debugbar.*',
            'horizon.*',
            'telescope.*',
            '_ignition.*',
            'livewire.*',
        ];
        
        $skipPaths = [
            '/favicon.ico',
            '/robots.txt',
            '/sitemap.xml',
            '/health',
            '/ping',
            '/status',
        ];
        
        $routeName = $request->route()?->getName();
        $path = $request->path();
        
        // Skip based on route name
        if ($routeName) {
            foreach ($skipRoutes as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return true;
                }
            }
        }
        
        // Skip based on path
        if (in_array('/' . $path, $skipPaths)) {
            return true;
        }
        
        // Skip asset requests
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine severity based on request and response
     */
    protected function determineSeverity(Request $request, int $statusCode): string
    {
        // Critical for security-related failures
        if ($statusCode === 401 || $statusCode === 403) {
            return 'critical';
        }
        
        // Warning for errors and sensitive operations
        if ($statusCode >= 400 || $this->isSensitiveRoute($request)) {
            return 'warning';
        }
        
        // Warning for destructive operations
        if (in_array($request->method(), ['DELETE', 'PUT', 'PATCH'])) {
            return 'warning';
        }
        
        // Warning for POST operations
        if ($request->method() === 'POST') {
            return 'warning';
        }
        
        return 'info';
    }

    /**
     * Check if route is sensitive
     */
    protected function isSensitiveRoute(Request $request): bool
    {
        $sensitivePatterns = [
            '*/login',
            '*/logout',
            '*/password/*',
            '*/users/*/toggle-status',
            '*/roles/*',
            '*/permissions/*',
            '*/export/*',
            '*/import/*',
            '*/settings/*',
            '*/admin/*',
        ];
        
        $path = $request->path();
        $routeName = $request->route()?->getName();
        
        foreach ($sensitivePatterns as $pattern) {
            if (fnmatch($pattern, $path) || fnmatch($pattern, $routeName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get module from route
     */
    protected function getModuleFromRoute(Request $request): string
    {
        $routeName = $request->route()?->getName();
        $path = $request->path();
        
        // Extract from route name
        if ($routeName && preg_match('/^([^.]+)\./', $routeName, $matches)) {
            return $matches[1];
        }
        
        // Extract from path
        if (preg_match('/^([^\/]+)/', $path, $matches)) {
            return $matches[1];
        }
        
        return 'web';
    }

    /**
     * Get request description
     */
    protected function getRequestDescription(Request $request, int $statusCode): string
    {
        $method = $request->method();
        $path = $request->path();
        $routeName = $request->route()?->getName() ?? 'unknown';
        
        $description = "{$method} request to {$path}";
        
        if ($routeName !== 'unknown') {
            $description .= " (route: {$routeName})";
        }
        
        $description .= " - Status: {$statusCode}";
        
        if (auth()->check()) {
            $description .= " by " . auth()->user()->name;
        }
        
        return $description;
    }

    /**
     * Get request tags
     */
    protected function getRequestTags(Request $request, int $statusCode): array
    {
        $tags = ['http_request', strtolower($request->method())];
        
        if ($statusCode >= 400) {
            $tags[] = 'error';
        }
        
        if ($statusCode === 401 || $statusCode === 403) {
            $tags[] = 'security';
            $tags[] = 'unauthorized';
        }
        
        if ($this->isSensitiveRoute($request)) {
            $tags[] = 'sensitive';
        }
        
        if (in_array($request->method(), ['DELETE', 'PUT', 'PATCH'])) {
            $tags[] = 'data_modification';
        }
        
        return $tags;
    }
}

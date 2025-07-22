<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if ($request->user()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log user activity
     */
    protected function logActivity(Request $request, Response $response): void
    {
        $user = $request->user();
        $method = $request->method();
        $path = $request->path();
        $statusCode = $response->getStatusCode();

        // Skip logging for certain routes (to avoid spam)
        $skipRoutes = [
            'api/user',
            'api/dashboard/quick-stats',
            'sanctum/csrf-cookie',
            'livewire/',
        ];

        foreach ($skipRoutes as $skipRoute) {
            if (str_starts_with($path, $skipRoute)) {
                return;
            }
        }

        // Skip successful GET requests (to avoid spam)
        if ($method === 'GET' && $statusCode === 200) {
            return;
        }

        // Log only important actions
        $importantMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        
        if (in_array($method, $importantMethods)) {
            $action = $this->getActionDescription($method, $path, $request);
            
            if ($action) {
                activity()
                    ->causedBy($user)
                    ->withProperties([
                        'method' => $method,
                        'path' => $path,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'status_code' => $statusCode,
                        'input' => $this->getSafeInput($request),
                    ])
                    ->log($action);
            }
        }
    }

    /**
     * Get action description based on method and path
     */
    protected function getActionDescription(string $method, string $path, Request $request): ?string
    {
        // Users actions
        if (str_contains($path, 'users')) {
            switch ($method) {
                case 'POST':
                    return str_contains($path, 'toggle-status') ? 'User status toggled' : 'User created';
                case 'PUT':
                case 'PATCH':
                    return 'User updated';
                case 'DELETE':
                    return 'User deleted';
            }
        }

        // Departments actions
        if (str_contains($path, 'departments')) {
            switch ($method) {
                case 'POST':
                    return str_contains($path, 'toggle-status') ? 'Department status toggled' : 'Department created';
                case 'PUT':
                case 'PATCH':
                    return 'Department updated';
                case 'DELETE':
                    return 'Department deleted';
            }
        }

        // Modules actions
        if (str_contains($path, 'modules')) {
            if (str_contains($path, 'enable')) {
                return 'Module enabled';
            }
            if (str_contains($path, 'disable')) {
                return 'Module disabled';
            }
            if (str_contains($path, 'clear-cache')) {
                return 'Module cache cleared';
            }
        }

        // Auth actions
        if (str_contains($path, 'login')) {
            return 'User logged in';
        }
        if (str_contains($path, 'logout')) {
            return 'User logged out';
        }
        if (str_contains($path, 'register')) {
            return 'User registered';
        }

        return null;
    }

    /**
     * Get safe input data (remove sensitive fields)
     */
    protected function getSafeInput(Request $request): array
    {
        $input = $request->all();
        
        // Remove sensitive fields
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_token',
            'secret',
            'key',
        ];

        foreach ($sensitiveFields as $field) {
            unset($input[$field]);
        }

        return $input;
    }
} 
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\User;
use App\Core\Services\ModuleService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $moduleService = app(ModuleService::class);

        $userData = null;
        $navigation = [];
        $modules = [];

        if ($user) {
            // ⛔ لا تستخدم $user مباشرة، حمّل نسخة جديدة نظيفة منه
            $freshUser = User::with(['roles.permissions', 'permissions', 'department'])->find($user->id);

            if ($freshUser) {
                $userData = [
                    'id' => $freshUser->id,
                    'name' => $freshUser->name,
                    'email' => $freshUser->email,
                    'first_name' => $freshUser->first_name,
                    'last_name' => $freshUser->last_name,
                    'avatar' => $freshUser->avatar,
                    'locale' => $freshUser->locale ?? 'en',
                    'timezone' => $freshUser->timezone ?? 'UTC',
                    'department' => $freshUser->department ? [
                        'id' => $freshUser->department->id,
                        'name' => $freshUser->department->name,
                        'code' => $freshUser->department->code,
                    ] : null,
                    'roles' => $freshUser->getRoleNames()->toArray(),
                    'permissions' => $freshUser->getAllPermissions()->pluck('name')->toArray(),
                ];

                // ✅ استخدم نفس النسخة للـ Navigation
                $navigation = $moduleService->getNavigationForUser($freshUser)->toArray();
                $modules = $moduleService->getActiveModules();
            }
        }

        return [
            ...parent::share($request),
            'auth' => ['user' => $userData],
            'navigation' => $navigation,
            'modules' => $modules,
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'locale' => app()->getLocale(),
            'locales' => config('app.available_locales', ['en', 'ar']),
        ];
    }
}
